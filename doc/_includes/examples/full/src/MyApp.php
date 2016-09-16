class MyApp extends Eole\Sandstone\Application
{
    public function __construct()
    {
        parent::__construct([
            'debug' => true,
        ]);

        // Sandstone requires JMS serializer
        $this->register(new Eole\Sandstone\Serializer\ServiceProvider());

        // Register and configure your websocket server
        $this->register(new Eole\Sandstone\Websocket\ServiceProvider(), [
            'sandstone.websocket.server' => [
                'bind' => '0.0.0.0',
                'port' => '25569',
            ],
        ]);

        // Register Push Server and ZMQ bridge extension
        $this->register(new \Eole\Sandstone\Push\ServiceProvider());
        $this->register(new \Eole\Sandstone\Push\Bridge\ZMQ\ServiceProvider(), [
            'sandstone.push.server' => [
                'bind' => '127.0.0.1',
                'host' => '127.0.0.1',
                'port' => 5555,
            ],
        ]);

        // Register serializer metadata
        $this['serializer.builder']->addMetadataDir(
            __DIR__,
            ''
        );
    }
}
