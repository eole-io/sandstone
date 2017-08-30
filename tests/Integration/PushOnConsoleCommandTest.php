<?php

namespace Eole\Sandstone\Tests\Integration;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\Request;
use Eole\Sandstone\Push\PushServerInterface;
use Eole\Sandstone\Tests\Integration\App\ArticleCreatedEvent;

class PushOnConsoleCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testDispatchingEventFromConsoleCommandTriggersPushEvent()
    {
        $pushServerMock = $this->getMockForAbstractClass(PushServerInterface::class);

        $app = new App\AppRestApi([
            'debug' => true,
            'sandstone.push' => function () use ($pushServerMock) {
                return $pushServerMock;
            },
        ]);

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

        $console = new App\Console($app);
        $input = new ArrayInput(['sandstone:test:push']);
        $console->find('sandstone:test:push')->run($input, new NullOutput());
    }
}
