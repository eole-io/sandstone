<?php

namespace Eole\Sandstone\Websocket\Event;

use Symfony\Component\EventDispatcher\Event;
use Ratchet\ConnectionInterface;

class ConnectionEvent extends Event
{
    /**
     * This event is dispatched when a websocket connection opens.
     * The listener will receive an instance of `ConnectionEvent`.
     *
     * @var string
     */
    const ON_OPEN = 'websocket_connection.on_open';

    /**
     * This event is dispatched when a websocket connection closes.
     * The listener will receive an instance of `ConnectionEvent`.
     *
     * @var string
     */
    const ON_CLOSE = 'websocket_connection.on_close';

    /**
     * This event is dispatched when a websocket has been authenticated,
     * and an user instance is available.
     * The listener will receive an instance of `WebsocketAuthenticationEvent`.
     *
     * @var string
     */
    const ON_AUTHENTICATION = 'websocket_connection.on_authentication';

    /**
     * This event is dispatched when an exception occurs during a websocket connection.
     * The listener will receive an instance of `ConnectionErrorEvent`.
     *
     * @var string
     */
    const ON_ERROR = 'websocket_connection.on_error';

    /**
     * This event is dispatched when someone subscribes to a wamp topic.
     * The listener will receive an instance of `WampEvent`.
     *
     * @var string
     */
    const ON_SUBSCRIBE = 'websocket_connection.on_subscribe';

    /**
     * This event is dispatched when someone unsubscribes to a wamp topic.
     * The listener will receive an instance of `WampEvent`.
     *
     * @var string
     */
    const ON_UNSUBSCRIBE = 'websocket_connection.on_unsubscribe';

    /**
     * This event is dispatched when someone publishes a message to a wamp topic.
     * The listener will receive an instance of `PublishEvent`.
     *
     * @var string
     */
    const ON_PUBLISH = 'websocket_connection.on_publish';

    /**
     * This event is dispatched on remote procedure call.
     * The listener will receive an instance of `RPCEvent`.
     *
     * @var string
     */
    const ON_RPC = 'websocket_connection.on_rpc';

    /**
     * @var ConnectionInterface
     */
    private $conn;

    /**
     * @param ConnectionInterface $conn
     */
    public function __construct(ConnectionInterface $conn)
    {
        $this->conn = $conn;
    }

    /**
     * @return ConnectionInterface
     */
    public function getConn()
    {
        return $this->conn;
    }
}
