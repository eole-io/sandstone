<?php

namespace Eole\Sandstone\Serializer;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['serializer.builder'] = function () use ($app) {
            $builder = SerializerBuilder::create()
                ->setDebug($app['debug'])
                ->addMetadataDir(__DIR__, 'Symfony\\Component\\EventDispatcher')
            ;

            return $builder;
        };

        $app['serializer'] = function () use ($app) {
            return $app['serializer.builder']->build();
        };
    }
}
