<?php

namespace Eole\Sandstone\Tests\Unit\Push;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Eole\Sandstone\Push\PushServerInterface;
use Eole\Sandstone\Push\EventSerializer;
use Eole\Sandstone\Push\EventForwarder;
use Eole\Sandstone\Tests\Integration\App\ArticleCreatedEvent;

class EventForwarderTest extends \PHPUnit_Framework_TestCase
{
    public function testForwardEventSendsExpectedMessageToPushServer()
    {
        $pushServerMock = $this->getMockForAbstractClass(PushServerInterface::class);
        $eventDispatcherMock = $this->getMockForAbstractClass(EventDispatcherInterface::class);
        $eventSerializerMock = $this->getMockBuilder(EventSerializer::class)->disableOriginalConstructor()->getMock();

        $eventForwarder = new EventForwarder($pushServerMock, $eventDispatcherMock, $eventSerializerMock);

        $event = new ArticleCreatedEvent(42, 'title', 'url');

        $pushServerMock
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function ($message) {
                $decodedMessage = unserialize($message);
                $serializedEvent = '{"propagation_stopped":false,"id":42,"title":"title","url":"url"}';

                return
                    ArticleCreatedEvent::ARTICLE_CREATED_EVENT === $decodedMessage['name'] &&
                    ArticleCreatedEvent::class === $decodedMessage['class'] &&
                    $serializedEvent === $decodedMessage['event']
                ;
            }))
        ;

        $eventSerializerMock
            ->expects($this->any())
            ->method('serializeEvent')
            ->with(
                $this->equalTo(ArticleCreatedEvent::ARTICLE_CREATED_EVENT),
                $this->equalTo($event)
            )
            ->willReturn('a:3:{s:4:"name";s:15:"article.created";s:5:"class";s:56:"Eole\Sandstone\Tests\Integration\App\ArticleCreatedEvent";s:5:"event";s:65:"{"propagation_stopped":false,"id":42,"title":"title","url":"url"}";}')
        ;

        $eventForwarder->forwardEvent($event, ArticleCreatedEvent::ARTICLE_CREATED_EVENT);
    }
}
