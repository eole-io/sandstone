<?php

namespace Eole\Sandstone\PushServer;

use ZMQ;
use ZMQContext;
use Pimple\ServiceProviderInterface;
use Pimple\Container;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * {@InheritDoc}
     */
    public function register(Container $app)
    {
        if (!$app->offsetExists('sandstone.push.enabled')) {
            $app['sandstone.push.enabled'] = true;
        }

        $app['sandstone.push.event_serializer'] = function () use ($app) {
            return new EventSerializer($app['serializer']);
        };

        $app['sandstone.push'] = function () use ($app) {
            $pushServerHost = $app['sandstone.push.server']['host'];
            $pushServerPort = $app['sandstone.push.server']['port'];

            $context = new ZMQContext();
            $socket = $context->getSocket(ZMQ::SOCKET_PUSH);
            $socket->connect("tcp://$pushServerHost:$pushServerPort");

            return $socket;
        };

        $app['sandstone.push.event_forwarder'] = function () use ($app) {
            return new EventForwarder(
                $app['sandstone.push'],
                $app['dispatcher'],
                $app['sandstone.push.event_serializer'],
                $app['sandstone.push.enabled']
            );
        };
    }
}
