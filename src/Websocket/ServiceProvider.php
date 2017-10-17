<?php

namespace Eole\Sandstone\Websocket;

use React\EventLoop\Factory;
use React\Socket\Server as ReactSocketServer;
use React\Socket\SecureServer as ReactSocketSecureServer;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Eole\Sandstone\Websocket\Routing\TopicRouter;
use Eole\Sandstone\Websocket\Routing\TopicCollection;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        if (!$app->offsetExists('sandstone.react_loop')) {
            $app['sandstone.react_loop'] = function () {
                return Factory::create();
            };
        }

        $app['sandstone.websocket.routes'] = function () {
            return new RouteCollection();
        };

        $app['sandstone.websocket.topics'] = function () use ($app) {
            return new TopicCollection($app['sandstone.websocket.routes']);
        };

        $app['sandstone.websocket.url_matcher'] = function () use ($app) {
            return new UrlMatcher(
                $app['sandstone.websocket.routes'],
                new RequestContext()
            );
        };

        $app['sandstone.websocket.router'] = function () use ($app) {
            return new TopicRouter($app['sandstone.websocket.url_matcher']);
        };

        $app['sandstone.websocket.socket'] = function () use ($app) {
            $serverConfig = $app['sandstone.websocket.server'];

            $websocketBind = $serverConfig['bind'];
            $websocketPort = $serverConfig['port'];

            $scheme = 'tcp';
            $context = [];

            if ($app->offsetExists('sandstone.tls') && true === $app['sandstone.tls']['enabled']) {
                var_dump('SCHEME TLS');
                $scheme = 'tls';
                $context['tls'] = $app['sandstone.tls'];
            }

            $socket = new ReactSocketServer(
                "$scheme://$websocketBind:$websocketPort",
                $app['sandstone.react_loop'],
                $context
            );

            $socket->on('connection', function ($connection) {
                echo 'Secure connection from ', $connection->getRemoteAddress(), PHP_EOL;
            });

            $socket->on('error', function ($e) {
                echo 'Error ', $e->getMessage(), PHP_EOL;
            });

            return $socket;
        };
    }
}
