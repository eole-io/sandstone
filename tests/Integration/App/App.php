<?php

namespace Eole\Sandstone\Tests\Integration\App;

use Silex\Provider;
use Eole\Sandstone\Push\Debug\PushServerProfilerServiceProvider;
use Eole\Sandstone\Application;

class App extends Application
{
    public function __construct(array $values = array())
    {
        parent::__construct($values);

        $this->register(new \Eole\Sandstone\Serializer\ServiceProvider());

        $this->register(new \Eole\Sandstone\Websocket\ServiceProvider(), [
            'sandstone.websocket.server' => [
                'bind' => '0.0.0.0',
                'port' => '8080',
            ],
        ]);

        $this['serializer.builder']->addMetadataDir(
            __DIR__,
            'Eole\\Sandstone\\Tests\\App'
        );

        $this->registerDebugProfiler();
    }

    private function registerDebugProfiler()
    {
        $this->register(new \Eole\Sandstone\Push\ServiceProvider());

        $this->register(new Provider\HttpFragmentServiceProvider());
        $this->register(new Provider\ServiceControllerServiceProvider());
        $this->register(new Provider\TwigServiceProvider());

        $this->register(new Provider\WebProfilerServiceProvider(), array(
            'profiler.cache_dir' => __DIR__.'/profiler',
            'profiler.mount_prefix' => '/_profiler',
        ));

        $this->register(new PushServerProfilerServiceProvider());
    }
}
