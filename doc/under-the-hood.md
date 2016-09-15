---
layout: page
---

<h2 class="no-margin-top">Under the hood</h2>

Sandstone is built on a few other cool PHP libraries you may want to check documentation:

- [Silex 2](http://silex.sensiolabs.org/) *for RestApi and application container ([Pimple](http://pimple.sensiolabs.org/))*
- [Ratchet PHP](http://socketo.me/) *for websockets*
- [ZeroMQ](http://zeromq.org/) *for Push server*
- [WAMP protocol](http://wamp-proto.org/) (**v1**) *for topics pub/sub*
- [JMS Serializer](http://jmsyst.com/libs/serializer) *to ensure serialization/deserialization between Sandstone components and client*
- [Symfony EventDispatcher](http://symfony.com/doc/current/components/event_dispatcher/introduction.html) *for Push server abstraction `->forwardEventToPushServer()`*
- [Symfony Routing](http://symfony.com/doc/current/components/routing/introduction.html) *for Topic declaration abstraction `$app->topic('chat{general}')`*


### WAMP protocol v1

As you can see, Sandstone uses the first version of WAMP protocol.

This choice has be done because at this time, **server-side**,
this version is better documented than the v2.

The library Ratchet PHP, which uses wamp v1, is well documented.
It allows to create topic classes extending `Ratchet\Wamp\Topic`,
and helps to structure the code.


### Push messages abstraction

There were a problem to send message from rest api to websocket server,
which are **two differents thread**.

To resolve this problem, I needed to use tcp sockets:
the websocket server opens and listen to a socket,
while the rest api send tcp messages to the socket.

First, I used ZMQ to abstract socket creation and listen with PHP.

But I don't want to use ZMQ directly in rest api controller to send messages,
and make controllers dependant to ZMQ.
<br>
I used Symfony EventDispatcher to abstract sending messages.

So messages becomes events, then we can dispatch events the usual way from controllers,
then Sandstone listens these events, serialize them using JMS serializer,
send them through socket, catch them the other side in the websocket server,
then re-disptach them using Symfony EventDispatcher.

That way, you can magically listen an event from a websocket topic,
that is sent from a rest api controller.

Note that Sandstone will not forward *all* events to websocket server,
just declare the ones you want to forward with `$app->forwardEventToPushServer('my_event')`.


### Websocket topics

I wanted to make topic declaration as simple as Silex does with routes.

How Silex declares a route:

``` php
$app = new Silex\Application();

// Or using Sandstone:
$app = new Eole\Sandstone\Application();

$app->get('/hello/{name}', function ($name) use ($app) {
    return 'Hello '.$app->escape($name);
});
```

How Sandstone declares a websocket topic:

``` php
$app = new Eole\Sandstone\Application();

$app->topic('chat/{channel}', function ($topicPattern, $arguments) {
    return new ChatTopic($topicPattern);
});
```

And you can also use constraints like:

``` php
$app
    ->topic('chat/{channel}', function ($topicPattern, $arguments) {
        return new ChatTopic($topicPattern);
    })
    ->value('channel', 'general')                   // Put a default value
    ->assert('channel', '^[a-zA-Z0-9]+$')           // Accept only characters for channel name
    ->convert('channel', function () { /* ... */ })
    ->before(function () { /* ... */ })
    ->when('chatEnabled()')
;
```

Then you get as arguments:

```
$topicPattern => 'chat/general', or 'chat/wtf-you-put-here'
$arguments => ['channel' => 'general']
```

