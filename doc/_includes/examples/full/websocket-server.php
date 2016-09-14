require_once 'vendor/autoload.php';

$app = new MyApp();

// Add a route `chat/{channel}` and its Topic factory (works same as mounting API endpoints)
$app->topic('chat/{channel}', function ($topicPattern) {
    return new ChatTopic($topicPattern);
});

// Encapsulate your application and start websocket server
$websocketServer = new Eole\Sandstone\Websocket\Server($app);

$websocketServer->run();
