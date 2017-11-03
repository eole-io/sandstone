---
layout: page
title: Sandstone | Websockets powered by PHP and Symfony
---

<h2 class="no-margin-top">Introduction</h2>

If you want to build a PHP application with real-time stuff,
this library can help you to use websockets in a structured application.

What you can achieve with Sandstone:

- Create a Rest Api
- Create a websocket topic the same way as creating an api endpoint
- Dispatch event from Rest Api to Websocket server


## Installation

Sandstone needs:

- PHP >= 5.6
- ZMQ and ZMQ php extension (or see [Install ZMQ and php-zmq on Linux]({{ site.baseurl }}/install-zmq-php-linux.html))

Sandstone is on composer ([eole/sandstone](https://packagist.org/packages/eole/sandstone)).
Installation using composer:

<pre class="command-line" data-prompt="$"><code class="language-bash">composer require eole/sandstone</code></pre>


## Usage


### Create a websocket topic

Declare a websocket topic just as easy as declaring a silex route:

``` php
$app = new Eole\Sandstone\Application();

$app->topic('chat/{channel}', function ($topicPattern, $arguments) {
    $channelName = $arguments['channel'];

    return new ChatTopic($topicPattern, $channelName);
});
```

> See more in the
> [Multichannel chat server example]({{ site.baseurl }}/examples/multichannel-chat.html).


### Send push notifications from RestApi

To send push notifications to web clients
when someone update a resource through the RestApi.

In rest api stack:

``` php
use Symfony\Component\HttpFoundation\Response;

$app = new Eole\Sandstone\Application();

// Creating an api endpoint at POST api/articles
$app->post('api/articles', function () use ($app) {
    $event = new ArticleEvent();

    $event->title = 'Unicorns spotted in Alaska';

    // Dispatch an event on article creation
    $app['dispatcher']->dispatch('article.created', $event);

    return new Response([], 201);
});

// Send all 'article.created' events to push server
$app->forwardEventToPushServer('article.created');
```

In websocket server stack:

``` php
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MyWebsocketTopic extends Eole\Sandstone\Websocket\Topic implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'article.created' => 'onArticleCreated',
        ];
    }

    public function onArticleCreated(ArticleEvent $event)
    {
        // Broadcast message on this topic when an article has been created.
        $this->broadcast([
            'message' => 'An article has just been published: '.$event->title,
        ]);
    }
}
```

> See more in the
> [Full example]({{ site.baseurl }}/examples/full.html).


## Sandstone edition

If you want to start a new project or a real-time RestApi,
you may consider using [Sandstone edition](https://github.com/eole-io/sandstone-edition).

This edition is a starter project with:

 - [Sandstone](https://eole-io.github.io/sandstone/) (Silex with websockets)
 - **Docker** environment to mount the whole application (RestApi, websocket server, MariaDB, PHPMyAdmin)
 - **Doctrine ORM** and Doctrine commands
 - **Symfony web profiler** for debugging RestApi requests and Push events
 - [Silex annotations](https://github.com/danadesrosiers/silex-annotation-provider) for controllers and routing annotations

It's for people who will need a real-time RestApi with a database and an ORM, debug tools,
and don't want to install all these tools and initiate the project structure (controllers, entities...).

It also has a Docker environment, so that you can install and run the app easily.

Check it out: <i class="fa fa-github fa-lg" aria-hidden="true"></i> [Sandstone edition](https://github.com/eole-io/sandstone-edition)


## Full working examples

See what you can do with examples:

- <i class="fa fa-code" aria-hidden="true"></i> [Full example of a rest api working with a websocket server]({{ site.baseurl }}/examples/full.html)
- <i class="fa fa-code" aria-hidden="true"></i> [Multichannel chat server]({{ site.baseurl }}/examples/multichannel-chat.html)


### Docker

A full example of a Sandstone application also exists as a Docker image.

Check it on Bitbucket: [zareba_pawel/php-websockets-sandstone](https://bitbucket.org/zareba_pawel/php-websockets-sandstone)


## License

Sandstone is under [MIT license](https://github.com/eole-io/sandstone/blob/master/LICENSE).
