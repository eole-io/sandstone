<?php

namespace Eole\Sandstone\Websocket\Routing;

use Symfony\Component\Routing\RouteCollection;
use Silex\Controller;
use Eole\Sandstone\Websocket\Routing\TopicRoute;

class TopicCollection
{
    /**
     * @var RouteCollection
     */
    private $routeCollection;

    /**
     * @param RouteCollection $routeCollection
     */
    public function __construct(RouteCollection $routeCollection)
    {
        $this->routeCollection = $routeCollection;
    }

    /**
     * Add a new topic route.
     *
     * @param string $pattern
     * @param callable $to
     *
     * @return Controller
     */
    public function match($pattern, callable $to)
    {
        $route = new TopicRoute($pattern, $to);
        $controller = new Controller($route);
        $routeName = $controller->generateRouteName('');

        $controller->bind($routeName);

        $this->routeCollection->add($routeName, $route);

        return $controller;
    }
}
