---
layout: page
title: Topic route parameters
---

<h1 class="no-margin-top">Topic route parameters</h1>

Declaring a single websocket topic with Sandstone is like:

``` php
$app->topic('chat/general', function ($topicPattern) {
    return new ChatTopic($topicPattern);
});
```

> **Note**: `$app` is your Sandstone application instance,
> created with `$app = new Eole\Sandstone\Application()`.
>
> See [Full example of Sandstone application]({{ site.baseurl }}/examples/full.html)
> to have an example to how to bootstrap a Sandstone application.

With the following `ChatTopic` class:

``` php
class ChatTopic extends Eole\Sandstone\Websocket\Topic
{
    // Where you can implement chat topic logic
    public function onPublish(Ratchet\Wamp\WampConnection $conn, $topic, $event) {}
    public function onSubscribe(Ratchet\Wamp\WampConnection $conn, $topic) {}
    public function onUnSubscribe(Ratchet\Wamp\WampConnection $conn, $topic) {}
}
```

> See [Example of Chat server]({{ site.baseurl }}/examples/multichannel-chat.html)
> to have a full exemple of this `ChatTopic`.


## Adding arguments in topic route

You may need to declare multiple chat topics,
i.e `chat/series`, `chat/manga`, `chat/gaming`, `chat/actu`, ...

So you have to make the topic name dynamic, with `chat/{channel}`:

``` php
$app->topic('chat/{channel}', function ($topicPattern) {
    return new ChatTopic($topicPattern);
});
```

With this example, you can use the same `ChatTopic` class
for every topic subscribed by JavaScript clients.

Sandstone will implement multiple topic instances of this same class
everytime someone subscribes or publish to a new `chat/{channel}`
which has not yet been used.

Then, when someone subscribes and/or publish on a new chat topic,
i.e `chat/my-new-topic`, Sandstone will instanciate a new websocket topic
from the `ChatTopic` (because `chat/my-new-topic` matches `chat/{channel}`)
with `my-new-topic` as route argument.


## Retrieve topic route arguments

Let's admit someone subscribes for the first time to `chat/my-new-topic` topic.
Sandstone will create a new instance of `ChatTopic`.

You may need to access to topic arguments, here `"my-new-topic"`,
in you `ChatTopic`.

Here is how to to do it: Sandstone passes route arguments in your topic callback
as an array, in a second argument.

So just retrieve the channel name in this array and pass it to your `ChatTopic` constructor:

``` php
$app->topic('chat/{channel}', function ($topicPattern, $arguments) {

    $channel = $arguments['channel'];

    return new ChatTopic($topicPattern, $channel);
});
```

Then, in your `ChatTopic` constructor:

``` php
class ChatTopic extends Eole\Sandstone\Websocket\Topic
{
    /**
     * @var string
     */
    private $channel;

    /**
     * @param string $topicPath
     * @param string $channel
     */
    public function __construct($topicPath, $channel)
    {
        parent::__construct($topicPath);

        $this->channel = $channel;
    }
}
```

You now have access to the `$channel` argument used in topic route `chat/{channel}`.
You can by example greeting new subscribers:

``` php
    public function onSubscribe(Ratchet\Wamp\WampConnection $conn, $topic)
    {
        parent::onSubscribe($conn, $topic);

        // Greeting new subscriber.
        $conn->event([
            'type' => 'greet',
            'message' => 'Hello, welcome on '.$this->channel',
        ]);

        // Broadcast everyone that someone joined the channel
        $this->broadcast([
            'type' => 'join',
            'message' => 'Someone has joined this channel.',
        ]);
    }
```


## Add constraints on route parameters

Declaring a topic route is the same as declaring an http route.

> **Note**: See Silex documentation about route configuration:
> [Routing Global Configuration](https://silex.symfony.com/index.php/doc/2.0/usage.html#global-configuration)

So you can add constraints, by example:

- Regex: allow only number for id

``` php
$app
    ->topic('games/{id}/moves-played', function ($topicPattern, $arguments) {
        return new GameTopic($topicPattern, $arguments['id']);
    })
    ->assert('id', '\d+')
;
```

- Regex: limit only some argument values

``` php
$app
    ->topic('chat/{channel}', function ($topicPattern, $arguments) {
        return new ChatTopic($topicPattern, $arguments['channel']);
    })
    ->assert('channel', '^(general|series|manga|gaming|actu)$')
;
```

- Make argument optional and add a default value

``` php
$app
    ->topic('chat/{channel}', function ($topicPattern, $arguments) {
        return new ChatTopic($topicPattern, $arguments['channel']);
    })
    ->value('channel', 'general')
;
```

- Using multiple rules at same time

``` php
$app
    ->topic('chat/{channel}', function ($topicPattern, $arguments) {
        return new ChatTopic($topicPattern, $arguments['channel']);
    })
    ->assert('channel', '^(general|series|manga|gaming|actu)$')
    ->value('channel', 'general')
;
```
