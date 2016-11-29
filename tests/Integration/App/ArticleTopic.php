<?php

namespace Eole\Sandstone\Tests\Integration\App;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Eole\Sandstone\Websocket\Topic;
use Eole\Sandstone\Tests\Integration\App\ArticleCreatedEvent;

class ArticleTopic extends Topic implements EventSubscriberInterface
{
    /**
     * {@InheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'article.created' => 'onArticleCreated',
        ];
    }

    /**
     * @param ArticleCreatedEvent $event
     */
    public function onArticleCreated(ArticleCreatedEvent $event)
    {
        $this->broadcast([
            'message' => 'An article has just been published: '.$event->title,
        ]);
    }
}
