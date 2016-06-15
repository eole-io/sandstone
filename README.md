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
        "eole/sandstone": "1.0.x"
    }
}
```

Then update your dependencies with `composer update`.


## Usage

Mounting a simple chat websocket topic:

``` php
use Eole\Sandstone\Application;

$app = new Application();

$app->topic('chat/general', function ($topicPath) {
    return new MyChatTopic($topicPath);
});
```

Use routing to mount multiple chat websocket topics:

``` php
$app->topic('chat/{channel}', function ($topicPath, $arguments) {
    return new MyChatTopic($topicPath, $arguments['channel']);
});
```


## License

This library is under [MIT License](LICENSE).
