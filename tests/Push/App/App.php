<?php

namespace Eole\Sandstone\Tests\Push\App;

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

        $this->register(new \Eole\Sandstone\Push\ServiceProvider());
    }
}
