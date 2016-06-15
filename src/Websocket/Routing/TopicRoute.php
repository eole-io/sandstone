<?php

namespace Eole\Sandstone\Websocket\Routing;

use Silex\Route;
use Eole\Sandstone\Websocket\Topic;

class TopicRoute extends Route
{
    /**
     * @param string $topicPath
     * @param string|Topic $topic topic or topic class name.
     * @param array $defaults
     * @param array $requirements
     */
    public function __construct($topicPath, $topic, array $defaults = array(), array $requirements = array())
    {
        $defaults['_topic'] = $topic;

        parent::__construct($topicPath, $defaults, $requirements);
    }
}
