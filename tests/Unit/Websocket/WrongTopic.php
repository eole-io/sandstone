<?php

namespace Eole\Sandstone\Tests\Unit\Websocket;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use Eole\Sandstone\Websocket\Topic;

class WrongTopic extends Topic implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [];
    }
}
