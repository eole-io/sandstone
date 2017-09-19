---
layout: page
title: Listen to websocket events
---

<h1 class="no-margin-top">Listen to websocket events</h1>

At some point of your running websocket server,
clients will connect and disconnect, and subscribe to a wamp topic to publish messages.

You may need to do something when one of these event happens.

Sandstone allows you to listen an event through the Silex event dispatcher.


## Which event I can listen to

All these events are dispatched:

| Event |  Event instance |
|-------|-----------------|
| `ConnectionEvent::ON_OPEN`            | [`ConnectionEvent`](https://github.com/eole-io/sandstone/blob/master/src/Websocket/Event/ConnectionEvent.php) |
| `ConnectionEvent::ON_CLOSE`           | [`ConnectionEvent`](https://github.com/eole-io/sandstone/blob/master/src/Websocket/Event/ConnectionEvent.php) |
| `ConnectionEvent::ON_AUTHENTICATION`  | [`WebsocketAuthenticationEvent`](https://github.com/eole-io/sandstone/blob/master/src/Websocket/Event/WebsocketAuthenticationEvent.php) |
| `ConnectionEvent::ON_ERROR`           | [`ConnectionErrorEvent`](https://github.com/eole-io/sandstone/blob/master/src/Websocket/Event/ConnectionErrorEvent.php) |
| `ConnectionEvent::ON_SUBSCRIBE`       | [`WampEvent`](https://github.com/eole-io/sandstone/blob/master/src/Websocket/Event/WampEvent.php) |
| `ConnectionEvent::ON_UNSUBSCRIBE`     | [`WampEvent`](https://github.com/eole-io/sandstone/blob/master/src/Websocket/Event/WampEvent.php) |
| `ConnectionEvent::ON_PUBLISH`         | [`PublishEvent`](https://github.com/eole-io/sandstone/blob/master/src/Websocket/Event/PublishEvent.php) |
| `ConnectionEvent::ON_RPC`             | [`RPCEvent`](https://github.com/eole-io/sandstone/blob/master/src/Websocket/Event/RPCEvent.php) |

### Details of events

| Event | When is it called | What can I do with it |
|-------|-------------------|-----------------------|
| `ON_OPEN`            | Someone connects to websocket server   | Retrieve some connection data with `$event->getConn()->Websocket` |
| `ON_CLOSE`           | Someone closed websocket connection    | *same as below* |
| `ON_AUTHENTICATION`  | Someone connected to websocket server and has been authenticated    | Retrieve authenticated user with `$event->getUser()` (returns Symfony `UserInterface`) |
| `ON_ERROR`           | An error has been triggered            | Retrieve the raised exception with `$event->getError()` (Returns an `\Exception`) |
| `ON_SUBSCRIBE`       | Someone subscribed to a topic          | Know which topic has been subscribed with `$event->getTopic()` |
| `ON_UNSUBSCRIBE`     | Someone unsubscribed to a topic        | *same with unsubscription* |
| `ON_PUBLISH`         | Someone published a message to a topic | Listen to published messages on topics with `$event->getEvent()` to get message, `$event->getTopic()` to get topic |
| `ON_RPC`             | Remote procedure call                  | Retrieve remote procedure call ID and parameters with `$event->getId()` and `$event->getParams()` |


## Listen to websocket events

I want to do something when someone connects to websocket server.

- First, create the listener

``` php
<?php

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Eole\Sandstone\Websocket\Event\ConnectionEvent;

class MyWebsocketListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            ConnectionEvent::ON_OPEN => 'onOpen',
        ];
    }

    public function onOpen(ConnectionEvent $event)
    {
        // do something when someone connects

        // retrieve some connection data with $event->getConn()->Websocket;
        // which returns a ConnectionInterface from RatchetPHP
    }
}
```

- Then, register this listener as a Silex `EventListenerProviderInterface`

``` php
<?php

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Pimple\Container;
use Silex\Api\EventListenerProviderInterface;

class MyProvider implements EventListenerProviderInterface
{
    public function subscribe(Container $app, EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addSubscriber(new MyWebsocketListener());
    }
}
```


## Another example: listen to published messages

I want to display (for debugging purpose) all published messages in any topic.

I have to listen to the `ConnectionEvent::ON_PUBLISH` event:

- First, create the listener

``` php
<?php

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Eole\Sandstone\Websocket\Event\ConnectionEvent;
use Eole\Sandstone\Websocket\Event\PublishEvent;

class MyMessagePublishListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            ConnectionEvent::ON_PUBLISH => 'onMessagePublish',
        ];
    }

    public function onMessagePublish(PublishEvent $event)
    {
        $message = $event->getEvent();
        $topic = $event->getTopic();

        echo "'$message' has been published in '$topic'", PHP_EOL;
    }
}
```

- Then, register this listener

``` php
<?php

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Pimple\Container;
use Silex\Api\EventListenerProviderInterface;

class MyProvider implements EventListenerProviderInterface
{
    public function subscribe(Container $app, EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addSubscriber(new MyMessagePublishListener());
    }
}
```

It should display in your websocket logs something like:

`'Hello world' has been published in 'general/chat'`


## Event class diagram

Here is the events diagram, all attributes are inherited:

<img
    src="img/events-dcm.png"
    alt="Sandstone websocket events DCM"
    class="img-responsive img-thumbnail"
/>
