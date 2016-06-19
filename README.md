# Sandstone

[![Latest Stable Version](https://poser.pugx.org/eole/sandstone/v/stable)](https://packagist.org/packages/eole/sandstone)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/914c7d8f-a51a-4146-b211-44bcf81f5b48/mini.png)](https://insight.sensiolabs.com/projects/914c7d8f-a51a-4146-b211-44bcf81f5b48)
[![License](https://poser.pugx.org/eole/sandstone/license)](https://packagist.org/packages/eole/sandstone)


Sandstone extends Silex to easily mount a RestApi working together with a **Websocket server**.
Also integrates a **Push server** to send messages from RestApi to websocket server.


## Installation

Requires:

 - PHP 5.5+
 - ZMQ and php zmq extension for Push Server


### Download

Using Composer ([eole/sandstone](https://packagist.org/packages/eole/sandstone)):

``` js
{
    "require": {
        "eole/sandstone": "~1.0"
    }
}
```

Then update your dependencies with `composer update`.


## Usage

### RestApi

Sandstone extends [Silex](http://silex.sensiolabs.org/).

Then, following Silex documentation, mounting a RestApi endpoint looks like that:

``` php
$app = new Eole\Sandstone\Application();

$app->post('api/articles', function () use ($app) {
    // Persisting article...

    $articleId = 42;
    $title = 'Unicorns spotted in Alaska';
    $url = 'http://unicorn.com/articles/unicorns-spotted-alaska';

    $app['dispatcher']->dispatch('article.created', new ArticleCreatedEvent($title, $url));

    return new JsonResponse($articleId, 201);
});

$app->run();
```


### Simple websocket server

Mounting a simple multichannel chat server:

**websocket-server.php**:
``` php
$app = new Eole\Sandstone\Application();

// Sandstone requires a JMS serializer
$app->register(new Eole\Sandstone\Serializer\ServiceProvider());

// Register and configure your websocket server
$app->register(new Eole\Sandstone\Websocket\ServiceProvider(), [
    'sandstone.websocket.server' => [
        'bind' => '0.0.0.0',
        'port' => '8080',
    ],
]);

// Add a route `chat/{channel}` and its Topic factory (works same as mounting API endpoints)
$app->topic('chat/{channel}', function ($topicPattern) {
    return new ChatTopic($topicPattern);
});

// Instanciate and start websocket server
$websocketServer = new Eole\Sandstone\Websocket\Server($app);

$websocketServer->run();
```

Then run your server:

``` shell
$ php websocket-server.php
Initialization...
Bind websocket server to 0.0.0.0:8080
```

**Great !** Now you have an operationnal chat server.


### Websocket Topic

A websocket topic is a sort of controller.

When a client has connected to your websocket server and subscribes to a topic,
Sandstone uses Symfony routing to affect a Topic instance to the given topic name.

Then if the Topic instance has not been created yet,
it is created using the Topic factory you provided.

How the `ChatTopic` class looks like:

``` php
class ChatTopic extends Eole\Sandstone\Websocket\Topic
{
    public function onPublish(Ratchet\Wamp\WampConnection $conn, $topic, $event)
    {
        $this->broadcast([
            'type' => 'message',
            'message' => $event,
        ]);
    }
}
```

In fact, it is based on [RatchetPHP](http://socketo.me/)'s [Topic](https://github.com/ratchetphp/Ratchet/blob/master/src/Ratchet/Wamp/Topic.php) class.

A `Topic` class must extends `Eole\Sandstone\Websocket\Topic`, and can override these methods:

 - `onPublish` when a message is received from a client,
 - `onSubscribe` when a new client subscribes to this topic,
 - `onUnSubscribe` when a client unsuscribes from this topic.

It also provides a `broadcast($message)` method to send a message to each subscribing clients.

So there is a full `ChatTopic` class example:

``` php
class ChatTopic extends Eole\Sandstone\Websocket\Topic
{
    /**
     * Broadcast message to each subscribing client.
     *
     * {@InheritDoc}
     */
    public function onPublish(Ratchet\Wamp\WampConnection $conn, $topic, $event)
    {
        $this->broadcast([
            'type' => 'message',
            'message' => $event,
        ]);
    }

    /**
     * Notify all subscribing clients that a new client has subscribed to this channel.
     *
     * {@InheritDoc}
     */
    public function onSubscribe(Ratchet\Wamp\WampConnection $conn, $topic)
    {
        parent::onSubscribe($conn, $topic);

        $this->broadcast([
            'type' => 'join',
            'message' => 'Someone has joined this channel.',
        ]);
    }

    /**
     * Notify all subscribing clients that a new client has unsubscribed from this channel.
     *
     * {@InheritDoc}
     */
    public function onUnSubscribe(Ratchet\Wamp\WampConnection $conn, $topic)
    {
        parent::onUnSubscribe($conn, $topic);

        $this->broadcast([
            'type' => 'leave',
            'message' => 'Someone has left this channel.',
        ]);
    }
}
```


### Using Websocket server together with RestApi

If you use both a RestApi and a websocket server,
you should note that the websocket server is launched from command,
while the RestApi, as a Silex application, is run from the web server.

As this is two different application stacks, you should now have a your services stack
in a base Application class:

``` php
class App extends Eole\Sandstone\Application
{
    public function __construct(array $values = array())
    {
        parent::__construct($values);

        $this->register(new Eole\Sandstone\Serializer\ServiceProvider());

        $this->register(new Eole\Sandstone\Websocket\ServiceProvider(), [
            'sandstone.websocket.server' => [
                'bind' => '0.0.0.0',
                'port' => '8080',
            ],
        ]);

        // Other services/stuff used both in RestApi and websocket server...
    }
}
```

Then your RestApi `index.php` file can now use `App` instead of `Eole\Sandstone\Application`:

``` php
$app = new App();

$app->post('api/articles', function () {
    // Persisting article...

    $articleId = 42;
    $title = 'Unicorns spotted in Alaska';
    $url = 'http://unicorn.com/articles/unicorns-spotted-alaska';

    $this['dispatcher']->dispatch('article.created', new ArticleCreatedEvent($title, $url));

    return new JsonResponse($articleId, 201);
});

$app->run();
```

And the same for `websocket-server.php` script file is simplified:

``` php
$app = new App();

$app->topic('chat/{channel}', function ($topicPattern) {
    return new ChatTopic($topicPattern);
});

$websocketServer = new Eole\Sandstone\Websocket\Server($app);

$websocketServer->run();
```

*Up to you to also refactor topics declaration, rest api endpoint... in a class,
which becomes highly recommended for scaling bigger applications.*

Now you have a Rest Api working with a websocket server,
an interessant part of Sandstone can be aborded.


### Push server

You may want to broadcast a message through a topic to all subscribing clients
when someone changed the state of the application by hitting your RestApi with a `POST` http request.

Sandstone integrates a ZMQ Push server, and is totally abstracted using Symfony event dispatcher:

You don't have to *send message to Push server*,
but instead you just *dispatch a simple event* through your application dispatcher.

Sandstone will automatically forward your event through Push server from RestApi,
and redispatch it to your websocket server, so that you just have to listen for your event from topics.


#### Register Push server and configuration

Register the `Eole\Sandstone\PushServer\ServiceProvider`:

``` php
class App extends Eole\Sandstone\Application
{
    public function __construct(array $values = array())
    {
        parent::__construct($values);

        // Register Push Server
        $this->register(new Eole\Sandstone\PushServer\ServiceProvider(), [
            'sandstone.push.enabled' => true,
            'sandstone.push.server' => [
                'bind' => '127.0.0.1',
                'host' => '127.0.0.1',
                'port' => '5555',
            ],
        ]);
    }
}
```

> **Note**:
> You should register the Push server service provider in your base application stack.


#### Use Push server

Just an example, you want to notify clients in chat channels when a new article is created:

1) **RestApi controller**: dispatch an event on article creation:

``` php
// In the RestApi controller (POST /api/articles)
$app['dispatcher']->dispatch('article.created', new ArticleCreatedEvent($title, $url));
```

2) **RestApi stack**: forward event to Push Server

``` php
// In the RestApi application stack
$app->forwardEventToPushServer('article.created');
```

*This step is necessary as not ALL events should be forwarded: it may be unwanted in certain cases.
So this line will automatically forward all future `article.created` events to Push server.*

3) **Websocket topic**: Listen to this event from your Topic

``` php
use Eole\Sandstone\Websocket\Topic;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

// implement EventSubscriberInterface
class ChatTopic extends Topic implements EventSubscriberInterface
{
    /**
     * Subscribe to article.created event.
     *
     * {@InheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'article.created' => 'onArticleCreated',
        ];
    }

    /**
     * Article created listener.
     *
     * @param ArticleCreatedEvent $event
     */
    public function onArticleCreated(ArticleCreatedEvent $event)
    {
        $this->broadcast([
            'type' => 'article_created',
            'message' => 'An article has just been created: '.$event->getTitle().', read it here: '.$event->getUrl(),
        ]);
    }
}
```

That was all.

By implementing `EventSubscriberInterface`,
Sandstone automatically register your `ChatTopic` to event subscriber.

Then, run all the websocket/push-server stack:

``` shell
$ php websocket-server.php
Initialization...
Bind websocket server to 0.0.0.0:8080
Bind push server to 127.0.0.1:5555
```


#### Note about Event serialization

As JMS Serializer is used, all class that will be serialized/deserialized
should have serialization metadata.

You then must provide class metadata, and register them to serializer:

1. Class metadata: See example with [Symfony Event class](src/Serializer/Event.yml), or [JMS Serializer reference](http://jmsyst.com/libs/serializer/master/reference)
2. Register metadata ([documentation about metadata locations](http://jmsyst.com/libs/serializer/master/configuration#configuring-metadata-locations)):

``` php
$app['serializer.builder']->addMetadataDir(
    __DIR__.'/your/metadata/folder',
    'Your\\Classes\\Namespace'
);
```

*Then you should map all your application model class this way.*

> **Note**:
> Register metadata should be done on your application services stack
> as it will be used in both rest api stack and websocket server stack.


## References

Sandstone is built on a few other cool PHP libraries you may want to check documentation:

- [Silex 2](http://silex.sensiolabs.org/) *for RestApi and application container ([Pimple](http://pimple.sensiolabs.org/))*
- [Ratchet PHP](http://socketo.me/) *for websockets*
- [ZeroMQ](http://zeromq.org/) *for Push server*
- [WAMP protocol](http://wamp-proto.org/) (**v1**) *for topics pub/sub*
- [JMS Serializer](http://jmsyst.com/libs/serializer) *to ensure serialization/deserialization between Sandstone components and client*
- [Symfony EventDispatcher](http://symfony.com/doc/current/components/event_dispatcher/introduction.html) *for Push server abstraction `->forwardEventToPushServer()`*
- [Symfony Routing](http://symfony.com/doc/current/components/routing/introduction.html) *for Topic declaration abstraction `$app->topic('chat{general}')` ;)*


## License

This library is under [MIT License](LICENSE).
