# Sandstone

Sandstone extends Silex to easily mount a RestApi working together with a **Websocket server**.
Also integrates a **Push server** to send messages from RestApi to websocket server.


## Installation

Requires:

 - PHP 5.5+
 - ZMQ and php zmq extension for Push Server


### Download

Using Composer:

``` js
{
    "require": {
        "eole/sandstone": "~1.0"
    }
}
```

Then update your dependencies with `composer update`.


## Usage

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


### Push server

Given you have built a RestApi and have websocket topics,
you may want to broadcast a message through a topic when someone
changed the state of the application by hitting your RestApi with a `POST` http request.

Sandstone integrates a ZMQ Push server.




## License

This library is under [MIT License](LICENSE).
