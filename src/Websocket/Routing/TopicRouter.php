<?php

namespace Eole\Sandstone\Websocket\Routing;

use LogicException;
use Eole\Sandstone\Websocket\Topic;
use Eole\Sandstone\Application;

class TopicRouter
{
    /**
     * @var Application
     */
    private $silexApp;

    /**
     * @param Application $silexApp
     */
    public function __construct(Application $silexApp)
    {
        $this->silexApp = $silexApp;
    }

    /**
     * @param string $topicPath
     *
     * @return Topic
     *
     * @throws LogicException when cannot load topic.
     */
    public function loadTopic($topicPath)
    {
        $urlMatcher = $this->silexApp['sandstone.websocket.url_matcher'];
        $arguments = $urlMatcher->match('/'.$topicPath);
        $topicFactory = $arguments['_topic'];

        if (!is_callable($topicFactory)) {
            throw new LogicException("Topic $topicPath is not a callback.");
        }

        $topicInstance = $topicFactory($topicPath, $arguments);

        if (!$topicInstance instanceof Topic) {
            throw new LogicException(sprintf(
                'Topic callback for %s must return an instance of %s.',
                $topicPath,
                Topic::class
            ));
        }

        return $topicInstance;
    }
}
