<?php

namespace Eole\Sandstone\Websocket\Event;

use Ratchet\ConnectionInterface;

class ConnectionErrorEvent extends ConnectionEvent
{
    /**
     * @var \Exception
     */
    private $error;

    /**
     * @param ConnectionInterface $conn
     * @param \Exception $error
     */
    public function __construct(ConnectionInterface $conn, \Exception $error)
    {
        parent::__construct($conn);

        $this->error = $error;
    }

    /**
     * @return ConnectionInterface
     */
    public function getError()
    {
        return $this->error;
    }
}
