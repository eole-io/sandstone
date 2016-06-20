<?php

namespace Eole\Sandstone\Websocket;

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
    }
}
