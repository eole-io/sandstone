require_once 'vendor/autoload.php';
require_once 'ChatTopic.php';

// Instanciate Sandstone
$app = new Eole\Sandstone\Application();

// Sandstone requires JMS serializer
$app->register(new Eole\Sandstone\Serializer\ServiceProvider());

// Register and configure your websocket server
$app->register(new Eole\Sandstone\Websocket\ServiceProvider(), [
    'sandstone.websocket.server' => [
        'bind' => '0.0.0.0',
        'port' => '25569',
    ],
]);

// Add a route `chat/{channel}` and its Topic factory (works same as mounting API endpoints)
$app->topic('chat/{channel}', function ($topicPattern) {
    return new ChatTopic($topicPattern);
});

// Encapsulate your application and run websocket server
$websocketServer = new Eole\Sandstone\Websocket\Server($app);

$websocketServer->run();
