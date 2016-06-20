<?php

namespace Eole\Sandstone\Websocket\Routing;

use LogicException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Eole\Sandstone\Websocket\Topic;

class TopicRouter
{
    /**
     * @var UrlMatcher
     */
    private $urlMatcher;

    /**
     * @param UrlMatcher $urlMatcher
     */
    public function __construct(UrlMatcher $urlMatcher)
    {
        $this->urlMatcher = $urlMatcher;
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
        $arguments = $this->urlMatcher->match('/'.$topicPath);
        $topicFactory = $arguments['_topic_factory'];

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
