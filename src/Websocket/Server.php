<?php

namespace Eole\Sandstone\Websocket;

use Psr\Log\LoggerAwareTrait;
use ZMQ;
use React\ZMQ\Context;
use React\EventLoop\LoopInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Wamp\ServerProtocol;
use Eole\Sandstone\Logger\EchoLogger;
use Eole\Sandstone\Application as SandstoneApplication;
use Eole\Sandstone\Websocket\Application as WebsocketApplication;

class Server
{
    use LoggerAwareTrait;

    /**
     * @var SandstoneApplication
     */
    private $sandstoneApplication;

    /**
     * @param SandstoneApplication $sandstoneApplication
     */
    public function __construct(SandstoneApplication $sandstoneApplication)
    {
        $this->sandstoneApplication = $sandstoneApplication;

        $this->setLogger(new EchoLogger());
    }

    /**
     * Init websocket server and push server if enabled.
     */
    private function initWebsocketServer()
    {
        $socket = $this->sandstoneApplication['sandstone.websocket.socket'];

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
            $socket,
            $this->getLoop()
        );
    }

    /**
     * Init push server and redispatch events from push server to application stack.
     */
    private function initPushServer()
    {
        $pushBind = $this->sandstoneApplication['sandstone.push.server']['bind'];
        $pushPort = $this->sandstoneApplication['sandstone.push.server']['port'];

        $context = new Context($this->getLoop());
        $pushServer = $context->getSocket(ZMQ::SOCKET_PULL);

        $pushServer->bind("tcp://$pushBind:$pushPort");

        $pushServer->on('message', function ($message) {
            $data = $this->sandstoneApplication['sandstone.push.event_serializer']->deserializeEvent($message);

            $this->logger->info('Push message event', ['event' => $data['name']]);

            $this->sandstoneApplication['dispatcher']->dispatch($data['name'], $data['event']);
        });
    }

    /**
     * Run websocket server.
     */
    public function run()
    {
        $this->logger->info('Initialization...');

        $this->initWebsocketServer();

        if ($this->sandstoneApplication->isPushEnabled()) {
            $this->initPushServer();
        }

        $this->sandstoneApplication->boot();

        $this->logger->info('Bind websocket server', $this->sandstoneApplication['sandstone.websocket.server']);

        if ($this->sandstoneApplication->isPushEnabled()) {
            $this->logger->info('Bind push server', $this->sandstoneApplication['sandstone.push.server']);
        }

        $this->getLoop()->run();
    }

    /**
     * @return LoopInterface
     */
    private function getLoop()
    {
        return $this->sandstoneApplication['sandstone.react_loop'];
    }
}
