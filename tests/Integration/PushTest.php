<?php

namespace Eole\Sandstone\Tests\Integration;

use Symfony\Component\HttpFoundation\Request;
use Eole\Sandstone\Push\PushServerInterface;
use Eole\Sandstone\Tests\Integration\App\ArticleCreatedEvent;

class PushTest extends \PHPUnit_Framework_TestCase
{
    public function testForwardEventToPushServer()
    {
        $pushServerMock = $this->getMockForAbstractClass(PushServerInterface::class);

        $app = new App\AppRestApi([
            'debug' => true,
            'sandstone.push' => function () use ($pushServerMock) {
                return $pushServerMock;
            },
        ]);

        $app->boot();

        $pushServerMock
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function ($message) {
                $decodedMessage = unserialize($message);
                $serializedEvent = '{"propagation_stopped":false,"id":42,"title":"Unicorns spotted in Alaska","url":"http:\/\/unicorn.com\/articles\/unicorns-spotted-alaska"}';

                return
                    ArticleCreatedEvent::ARTICLE_CREATED_EVENT === $decodedMessage['name'] &&
                    ArticleCreatedEvent::class === $decodedMessage['class'] &&
                    $serializedEvent === $decodedMessage['event']
                ;
            }))
        ;

        $result = $app->handle(Request::create('/api/articles', Request::METHOD_POST));

        $this->assertEquals(42, $result->getContent());
    }
}
