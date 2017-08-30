<?php

namespace Eole\Sandstone\Tests\Unit\Websocket;

use Eole\Sandstone\Serializer\ServiceProvider as SerializerServiceProvider;
use Eole\Sandstone\Websocket\Routing\TopicRouter;
use Eole\Sandstone\Websocket\Application as WebsocketApplication;
use Eole\Sandstone\Application;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadTopicThrowExceptionWhenImplementingTheWrongEventDispatcherInterface()
    {
        $app = new Application();
        $websocketAppClass = new \ReflectionClass(WebsocketApplication::class);
        $method = $websocketAppClass->getMethod('loadTopic');
        $method->setAccessible(true);
        $websocketApp = $websocketAppClass->newInstance($app);

        $app->register(new SerializerServiceProvider());

        $app['sandstone.websocket.router'] = function () {
            $wrongTopic = new WrongTopic('my-topic');
            $topicRouterMock = $this->createMock(TopicRouter::class);

            $topicRouterMock
                ->method('loadTopic')
                ->willReturn($wrongTopic)
            ;

            return $topicRouterMock;
        };

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('WrongTopic seems to implements the wrong EventSubscriberInterface');

        $method->invokeArgs($websocketApp, ['my-topic']);
    }
}
