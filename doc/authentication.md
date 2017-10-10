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
Sandstone provide an [OAuth2](https://github.com/thephpleague/oauth2-server)
provider.


## Sandstone authentication

The Sandstone OAuth2 provider allows to use same authentication tokens for a
Rest API request and a websocket connection.

The idea is that you first get an OAuth access token from the Rest API
using your username and password.
You then receive an access token you can use to:

- authenticate your Rest API requests by adding it in headers:

```
GET /api/hello

Authorization: Bearer L83pR5amKt
```

- authenticate your websocket connection at the opening:

``` js
websocket.connect('ws://localhost:8482?access_token=L83pR5amKt')
```

You'll then see in your websocket server logs:

```
[info] Authentication... []
[info] User logged. {"username":"alcalyn"}
```





> **Debug**: You can enable the Security panel in your Symfony profiler
> by adding `symfony/security-bundle` in your dependencies.
>
> Just do `composer require symfony/security-bundle`, then make an API call.



