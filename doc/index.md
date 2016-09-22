---
layout: page
---

<h2 class="no-margin-top">Introduction</h2>

If you want to build a PHP application with real-time stuff,
this library can help you to use websockets in a structured application.

What you can achieve with Sandstone:

- Create a Rest Api using Silex (Sandstone extends Silex)
- Create a websocket topic the same way as creating an api endpoint
- Dispatch event from Rest Api to Websocket server using Symfony event dispatcher


## Installation

Sandstone needs:

- PHP >= 5.5
- ZMQ and ZMQ php extension (or see [Install ZMQ and php-zmq on Linux]({{ site.baseurl }}/install-zmq-php-linux.html))

Sandstone is on composer ([eole/sandstone](https://packagist.org/packages/eole/sandstone)).
Installation using composer:

<pre class="command-line" data-prompt="$"><code class="language-bash">composer require eole/sandstone</code></pre>


## Usage


### Create a websocket topic

``` php
$app = new Eole\Sandstone\Application();

$app->topic('chat/{channel}', function ($topicPattern, $arguments) {
    $channelName = $arguments['channel'];

    return new ChatTopic($topicPattern, $channelName);
});
```


### Dispatch an event from rest api to a websocket server topic

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


### Full working examples

See what you can do with examples:

- [Full example of a rest api working with a websocket server]({{ site.baseurl }}/examples/full.html)
- [Multichannel chat server]({{ site.baseurl }}/examples/multichannel-chat.html)


## License

Sandstone is under [MIT license](https://github.com/eole-io/sandstone/blob/master/LICENSE).
