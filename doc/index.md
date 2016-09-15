---
layout: home
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
Using composer:

``` json
{
    "require": {
        "eole/sandstone": "1.x"
    }
}
```

Then run `composer update`.


## Usage

Creating a multichannel chat server:

``` php
// chat-server.php

require_once 'vendor/autoload.php';

$app = new Eole\Sandstone\Application();

$app->topic('chat/{channel}', function ($topicPattern, $arguments) {
    $channelName = $arguments['channel'];

    return new ChatTopic($topicPattern, $channelName);
})
->assert('channel', '^[a-zA-Z0-9]+$');

$websocketServer = new Eole\Sandstone\Websocket\Server($app);

$websocketServer->run();
```

Then run chat server with `php chat-server.php`.

But Sandstone is not only a framework to use websockets.
It is meant to create a rest api working together with websocket server.

See the <a href="{{ site.baseurl }}/examples/full.html">full example of Sandstone</a>.


## License

Sandstone is under [MIT license](https://github.com/eole-io/sandstone/blob/master/LICENSE).
