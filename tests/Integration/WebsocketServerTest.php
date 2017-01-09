<?php

namespace Eole\Sandstone\Tests\Integration;

use Psr\Log\NullLogger;
use Ratchet\Wamp\WampConnection;

class WebsocketServerTest extends \PHPUnit_Framework_TestCase
{
    public function testWebsocketServerOnSubscribe()
    {
        $app = new App\App();

        $app->topic('articles', function ($topicPattern) {
            return new App\ArticleTopic($topicPattern);
        });

        $wsApp = new \Eole\Sandstone\Websocket\Application($app);
        $wsApp->setLogger(new NullLogger());

        $connectionMock = $this->createMock(WampConnection::class);

        $wsApp->onSubscribe($connectionMock, 'articles');
    }
}
