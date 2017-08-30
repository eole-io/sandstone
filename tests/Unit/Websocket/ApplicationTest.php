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
            $topicRouterMock = $this->getMockBuilder(TopicRouter::class)->disableOriginalConstructor()->getMock();

            $topicRouterMock
                ->method('loadTopic')
                ->willReturn($wrongTopic)
            ;

            return $topicRouterMock;
        };

        $this->setExpectedExceptionRegExp(
            \LogicException::class,
            '/WrongTopic seems to implements the wrong EventSubscriberInterface/'
        );

        $method->invokeArgs($websocketApp, ['my-topic']);
    }
}
