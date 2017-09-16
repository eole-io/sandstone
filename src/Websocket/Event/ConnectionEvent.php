<?php

namespace Eole\Sandstone\Websocket\Event;

use Symfony\Component\EventDispatcher\Event;
use Ratchet\ConnectionInterface;

class ConnectionEvent extends Event
{
    /**
     * @var string
     */
    const ON_OPEN = 'websocket_connection.on_open';

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
