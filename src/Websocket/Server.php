<?php

namespace Eole\Sandstone\Websocket;

use ZMQ;
use React\ZMQ\Context;
use React\EventLoop\LoopInterface;
use React\EventLoop\Factory;
use React\Socket\Server as ReactSocketServer;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\Websocket\WsServer;
use Ratchet\Wamp\ServerProtocol;
use Eole\Sandstone\Application as SandstoneApplication;
use Eole\Sandstone\Websocket\Application as WebsocketApplication;

class Server
{
    /**
     * @var SandstoneApplication
     */
    private $sandstoneApplication;

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @param SandstoneApplication $sandstoneApplication
     */
    public function __construct(SandstoneApplication $sandstoneApplication)
    {
        $this->sandstoneApplication = $sandstoneApplication;
        $this->loop = Factory::create();

        echo 'Initialization...'.PHP_EOL;

        $this->initWebsocketServer();

        if ($this->sandstoneApplication->isPushServerEnabled()) {
            $this->initPushServer();
        }
    }

    /**
     * Init websocket server and push server if enabled.
     */
    private function initWebsocketServer()
    {
        $websocketBind = $this->sandstoneApplication['sandstone.websocket.server']['bind'];
        $websocketPort = $this->sandstoneApplication['sandstone.websocket.server']['port'];

        $socket = new ReactSocketServer($this->loop);
        $socket->listen($websocketPort, $websocketBind);

        new IoServer(
            new HttpServer(
                new WsServer(
                    new ServerProtocol(
                        new WebsocketApplication(
                            $this->sandstoneApplication
                        )
                    )
                )
            ),
            $socket
        );
    }

    /**
     * Init push server and redispatch events from push server to application stack.
     */
    private function initPushServer()
    {
        $pushBind = $this->sandstoneApplication['sandstone.push.server']['bind'];
        $pushPort = $this->sandstoneApplication['sandstone.push.server']['port'];

        $context = new Context($this->loop);
        $pushServer = $context->getSocket(ZMQ::SOCKET_PULL);

        $pushServer->bind("tcp://$pushBind:$pushPort");

        $pushServer->on('message', function ($message) {
            $data = $this->sandstoneApplication['sandstone.push.event_serializer']->deserializeEvent($message);

            echo 'PushServer message event: '.$data['name'].PHP_EOL;

            $this->sandstoneApplication['dispatcher']->dispatch($data['name'], $data['event']);
        });
    }

    /**
     * Run websocket server.
     */
    public function run()
    {
        $this->sandstoneApplication->boot();

        $websocketBind = $this->sandstoneApplication['sandstone.websocket.server']['bind'];
        $websocketPort = $this->sandstoneApplication['sandstone.websocket.server']['port'];

        echo "Bind websocket server to $websocketBind:$websocketPort".PHP_EOL;

        if ($this->sandstoneApplication->isPushServerEnabled()) {
            $pushBind = $this->sandstoneApplication['sandstone.push.server']['host'];
            $pushPort = $this->sandstoneApplication['sandstone.push.server']['port'];

            echo "Bind push server to $pushBind:$pushPort".PHP_EOL;
        }

        $this->loop->run();
    }
}
