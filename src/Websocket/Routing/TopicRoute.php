<?php

namespace Eole\Sandstone\Websocket\Routing;

use Silex\Route;

class TopicRoute extends Route
{
    /**
     * @param string $topicPath
     * @param callable $topicFactory
     * @param array $defaults
     * @param array $requirements
     */
    public function __construct($topicPath, callable $topicFactory, array $defaults = array(), array $requirements = array())
    {
        $defaults['_topic_factory'] = $topicFactory;

        parent::__construct($topicPath, $defaults, $requirements);
    }
}
