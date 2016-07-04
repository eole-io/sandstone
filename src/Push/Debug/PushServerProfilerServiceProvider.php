<?php

namespace Eole\Sandstone\Push\Debug;

use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Eole\Sandstone\Push\Debug\DataCollector\PushServerDataCollector;

class PushServerProfilerServiceProvider implements ServiceProviderInterface
{
    /**
     * {@InheritDoc}
     */
    public function register(Container $app)
    {
        $app->extend('data_collectors', function ($collectors) {
            $collectors[PushServerDataCollector::NAME] = function ($app) {
                return new PushServerDataCollector($app['sandstone.push']);
            };

            return $collectors;
        });

        $app['data_collector.templates'] = $app->extend('data_collector.templates', function ($templates) {
            $templates []= array(
                PushServerDataCollector::NAME,
                'push-messages.html.twig',
            );

            return $templates;
        });

        $app['twig.loader.filesystem'] = $app->extend('twig.loader.filesystem', function ($loader) {
            $loader->addPath(__DIR__.'/DataCollector/views');

            return $loader;
        });
    }
}
