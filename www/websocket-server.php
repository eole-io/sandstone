<?php

require_once '../vendor/autoload.php';
require_once 'App.php';
require_once 'ChatTopic.php';

$app = new App();

// Add a route `chat/{channel}` and its Topic factory (works same as mounting API endpoints)
$app->topic('chat/{channel}', function ($topicPattern) {
    return new ChatTopic($topicPattern);
});

// Encapsulate your application and start websocket server
$websocketServer = new Eole\Sandstone\Websocket\Server($app);

$websocketServer->run();
