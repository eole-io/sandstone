<?php

namespace Eole\Sandstone\Serializer;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Eole\Sandstone\Websocket\Routing\TopicRouter;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['serializer.builder'] = function () use ($app) {
            return SerializerBuilder::create()
                ->setCacheDir($app['serializer.cache_dir'])
                ->setDebug($app['debug'])
                ->addMetadataDir(__DIR__)
            ;
        };

        $app['serializer'] = function () use ($app) {
            return $app['serializer.builder']->build();
        };
    }
}
