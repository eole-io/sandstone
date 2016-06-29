<?php

namespace Eole\Sandstone\Push\Bridge\ZMQ;

use ZMQ;
use ZMQContext;
use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Eole\Sandstone\Push\Debug\TraceablePushServer;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * {@InheritDoc}
     */
    public function register(Container $app)
    {
        $app['sandstone.push'] = function () use ($app) {
            $pushServerHost = $app['sandstone.push.server']['host'];
            $pushServerPort = $app['sandstone.push.server']['port'];

            $context = new ZMQContext();
            $socket = $context->getSocket(ZMQ::SOCKET_PUSH);
            $socket->connect("tcp://$pushServerHost:$pushServerPort");

            $pushServer = new ZMQPushServer($socket);

            if ($app['debug']) {
                $pushServer = new TraceablePushServer($pushServer);
            }

            return $pushServer;
        };
    }
}
