---
layout: page
title: Authentication
---

<h1 class="no-margin-top">Authentication</h1>

If your application has users, you maybe need to provide them a way to authenticate
their Rest API requests, and also their websocket connections.

As Sandstone uses Symfony, you can securize RestAPI requests as described in the
documentation. See [Silex security](https://silex.symfony.com/doc/2.0/providers/security.html).

For an authenticated websocket connection,
Sandstone provide an OAuth2 provider based on the library
[thephpleague/oauth2-server](https://github.com/thephpleague/oauth2-server).


## Sandstone authentication

The Sandstone OAuth2 provider allows to use same authentication tokens for a
Rest API request and a websocket connection.

The idea is that you first get an OAuth access token from the Rest API
using your username and password.
You then receive an access token you can use to:

- authenticate your Rest API requests by adding it in headers:

``` http
GET /api/hello

Authorization: Bearer L83pR5amKt
```

- authenticate your websocket connection at the opening:

``` js
websocket.connect('ws://localhost:8482?access_token=L83pR5amKt')
```


## Implementation


### Symfony authentication

If you already configured your Symfony security firewall, you can skip this step
and use your own configuration.

Otherwise, let's implement a basic authentication system. Here using Symfony's
`InMemoryUserProvider`, I'll create a hardcoded user in configuration.
Up to you to implement your own Symfony `UserProvider`, see
[How to Create a custom User Provider](https://symfony.com/doc/current/security/custom_provider.html).

``` php
use Symfony\Component\Security\Core\User\InMemoryUserProvider;
use Silex\Provider\SecurityServiceProvider;
use Eole\Sandstone\Application as SandstoneApplication;

$app = new SandstoneApplication([
    'project.root' => dirname(__DIR__),
    'env' => 'dev',
    'debug' => true,
]);

$app['app.user_provider'] = function () {
    return new InMemoryUserProvider([
        // username: admin / password: foo
        'admin' => [
            'roles' => ['ROLE_ADMIN'],
            'password' => '$2y$10$3i9/lVd8UOFIJ6PAMFt8gu3/r5g0qeCJvoSlLCsvMTythye19F77a',
        ],
    ]);
};

$app->register(new SecurityServiceProvider(), [
    'security.firewalls' => [
        'api' => [
            'pattern' => '^/api',
            'anonymous' => true,
            'http' => true,
            'users' => $app['app.user_provider'],
        ],
    ],
]);

$app->run();
```

I configured the `api` firewall which securize all routes starting with `/api`.
In my Sandstone application, it represents the whole Rest API.
This firewall allows anonymous requests (`'anonymous' => true`),
so authentication is optional unless I add a condition in my controllers.

> I also added `'http' => true` to allows basic authentication for the following example.

> **Note**: This example is based on Silex security documentation.
> See [Silex security](https://silex.symfony.com/doc/2.0/providers/security.html).

Now I can already authenticate my Rest API requests. Let's test it by
adding the `GET /api/hello` route:

``` php
use Symfony\Component\HttpFoundation\JsonResponse;

$app->get('api/hello', function () use ($app) {
    $result = [
        'authenticated' => $app['user'] ? $app['user']->getUsername() : '*nope*',
    ];

    return new JsonResponse($result);
});
```

If I requests it without authentication, I get:

``` http
# Request
GET /api/hello

# Response
{
    "authenticated": "*nope*"
}
```

By authenticating `admin:foo` with basic auth, I get:

``` http
# Request
GET /api/hello

Authorization: Basic YWRtaW46Zm9v

# Response
{
    "authenticated": "admin"
}
```

> The string `YWRtaW46Zm9v` is the base64 encoded of `admin:foo`.
> See [Basic access authentication](https://en.wikipedia.org/wiki/Basic_access_authentication).


### Register Sandstone OAuth2

In order to authenticate websocket connections, you have to register Sandstone's
OAuth2 provider with some configuration:

``` php
use Eole\Sandstone\OAuth2\Silex\OAuth2ServiceProvider;

$app->register(new OAuth2ServiceProvider(), [
    'oauth.firewall_name' => 'api',
    'oauth.security.user_provider' => 'app.user_provider',
    'oauth.tokens_dir' => $app['project.root'].'/var/oauth-tokens',
    'oauth.scope' => [
        'id' => 'sandstone-scope',
        'description' => 'Sandstone scope.',
    ],
    'oauth.clients' => [
        'my-web-application' => [
            'name' => 'my-app-name',
            'id' => 'my-app',
            'secret' => 'my-app-secret',
        ],
    ],
]);
```

Configuration details:

- `oauth.firewall_name` is the firewall to use to securize websocket connections
- `oauth.security.user_provider` set your user provider service name
- `oauth.tokens_dir` folder to store oauth tokens
- `oauth.scope` the scope of your oauth token
- `oauth.clients` your api keys for clients who use oauth

**You also need to update your firewall configuration** to make authentication
stateless and use oauth:

``` php
    'security.firewalls' => [
        'api' => [
            // ...
            'oauth' => true,
            'stateless' => true,
        ],
    ],
```

> **Note**:
> See [Stateless Authentication](https://silex.symfony.com/doc/2.0/providers/security.html#stateless-authentication)
> on Silex documentation.

You can now test your OAuth, but you first need a way to issue an access token
from the Rest API.


### Create a controller to get an access token

Sandstone OAuth component provides a service to issue a token. You have to call
`$app['sandstone.oauth.controller']->postAccessToken($request)` from a controller,
and return the token to the client.

Here is an example of a controller:

``` php
use League\OAuth2\Server\Exception\OAuthException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

$app->post('/oauth/access-token', function (Request $request) use ($app) {
    try {
        $token = $app['sandstone.oauth.controller']->postAccessToken($request);

        return new JsonResponse($token);
    } catch (OAuthException $e) {
        return new JsonResponse([
            'oauth_error_type' => $e->errorType,
            'message' => $e->getMessage(),
            'parameter' => $e->getParameter(),
            'should_redirect' => $e->shouldRedirect(),
            'redirect' => $e->redirectUri,
        ], $e->httpStatusCode);
    }
});
```

> **Note**: Make the access token route **outside** the `api` firewall so that
> getting an access token is not behind OAuth security.
> In this example, the access token route prefix is `/oauth/`
> whereas the api routes prefixes are `/api/`.


### Get an OAuth access token

An access token is a string like `VBLQa98foR2dOSJpb9bugh00e1r7H74manwEhmbj`.
It's generated from the server, and associated to an user.
It has an expiration time, so you have to get another one before it expires.

Sandstone also uses refresh tokens. You then get also a refresh token
you may store when you get an access token.

> See [Which OAuth 2.0 grant should I implement?](https://oauth2.thephpleague.com/authorization-server/which-grant/)
> from thephpleague oauth2 server documentation.

You can get an access token from your Rest API by using a grant type.

#### Password grant type

Get an access token with your username and password. It's usefull for the first
connection.

``` http
POST /oauth/access-token

Content-Type: application/x-www-form-urlencoded

grant_type=password&client_id=my-app&client_secret=my-app-secret&username=admin&password=foo
```

Response example:

``` js
{
    "access_token": "VBLQa98foR2dOSJpb9bugh00e1r7H74manwEhmbj",
    "token_type": "Bearer",
    "expires_in": 3600,
    "refresh_token": "JyrPAZZU17EDYIFSxnKmsX3HzwxGSW1fijEu5rsL"
}
```

#### Refresh Token grant type

Get an access token from your last refresh token. It's usefull to not resend
your secret credentials. Also you won't need to store user password in your
client application, but only the refresh token.

``` http
POST /oauth/access-token

Content-Type: application/x-www-form-urlencoded

grant_type=refresh_token&client_id=my-app&client_secret=my-app-secret&refresh_token=JyrPAZZU17EDYIFSxnKmsX3HzwxGSW1fijEu5rsL
```

You'll get a similar response as the example above, with a new access token and
refresh token.


### Use the access token


#### In Rest API requests

``` http
GET /api/hello

Authorization: Bearer VBLQa98foR2dOSJpb9bugh00e1r7H74manwEhmbj
```


#### In websocket connection

``` js
websocket.connect('ws://localhost:8482?access_token=VBLQa98foR2dOSJpb9bugh00e1r7H74manwEhmbj')
```

You'll then see in your websocket server logs:

```
[info] Authentication... []
[info] User logged. {"username":"alcalyn"}
```


## Enable the security panel in the profiler

You can enable the Security panel in your Symfony profiler
by adding `symfony/security-bundle` in your dependencies.

Just do:

``` bash
composer require symfony/security-bundle
```

Make an Api call, then you should see the new panel by profiling the new request.

> See on Github [silexphp/Silex-WebProfiler](https://github.com/silexphp/Silex-WebProfiler)


## Full authenticated application example

Here is the full working example with an OAuth authentication.

``` php
require_once '../vendor/autoload.php';

use League\OAuth2\Server\Exception\OAuthException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\InMemoryUserProvider;
use Silex\Provider\SecurityServiceProvider;
use Eole\Sandstone\OAuth2\Silex\OAuth2ServiceProvider;
use Eole\Sandstone\Application as SandstoneApplication;

$app = new SandstoneApplication([
    'project.root' => dirname(__DIR__),
    'env' => 'dev',
    'debug' => true,
]);

$app['app.user_provider'] = function () {
    return new InMemoryUserProvider([
        // username: admin / password: foo
        'admin' => [
            'roles' => ['ROLE_ADMIN'],
            'password' => '$2y$10$3i9/lVd8UOFIJ6PAMFt8gu3/r5g0qeCJvoSlLCsvMTythye19F77a',
        ],
    ]);
};

$app->register(new SecurityServiceProvider(), [
    'security.firewalls' => [
        'api' => [
            'pattern' => '^/api',
            'oauth' => true,
            'stateless' => true,
            'anonymous' => true,
            'users' => $app['app.user_provider'],
        ],
    ],
]);

$app->register(new OAuth2ServiceProvider(), [
    'oauth.firewall_name' => 'api',
    'oauth.security.user_provider' => 'app.user_provider',
    'oauth.tokens_dir' => $app['project.root'].'/var/oauth-tokens',
    'oauth.scope' => [
        'id' => 'sandstone-scope',
        'description' => 'Sandstone scope.',
    ],
    'oauth.clients' => [
        'my-web-application' => [
            'name' => 'my-app-name',
            'id' => 'my-app',
            'secret' => 'my-app-secret',
        ],
    ],
]);

$app->post('/oauth/access-token', function (Request $request) use ($app) {
    try {
        $token = $app['sandstone.oauth.controller']->postAccessToken($request);

        return new JsonResponse($token);
    } catch (OAuthException $e) {
        return new JsonResponse([
            'oauth_error_type' => $e->errorType,
            'message' => $e->getMessage(),
            'parameter' => $e->getParameter(),
            'should_redirect' => $e->shouldRedirect(),
            'redirect' => $e->redirectUri,
        ], $e->httpStatusCode);
    }
});

$app->get('api/hello', function () use ($app) {
    $result = [
        'authenticated' => $app['user'] ? $app['user']->getUsername() : '*nope*',
    ];

    return new JsonResponse($result);
});

$app->run();
```

Here is the Postman collection:

[<i class="fa fa-download fa-lg" aria-hidden="true"></i>
sandstone_oauth.postman_collection.json](assets/sandstone_oauth.postman_collection.json)

> [Postman](https://www.getpostman.com/) allows to run presaved requests to an
> API. Import this file and you'll get requests examples to your Sandstone
> application with OAuth.

Here is an example of websocket connection with an access token using Javascript:

``` js
ab.connect('ws://0.0.0.0:8482?access_token=L83pR5amKtRdaTo3hTBsaD7tp8tcWWCCsKgGMH9M')
```

> I use here AutobahnJS 0.8
