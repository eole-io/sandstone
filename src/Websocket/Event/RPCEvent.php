<?php

namespace Eole\Sandstone\Websocket\Event;

use Ratchet\Wamp\Topic;
use Ratchet\ConnectionInterface;

class RPCEvent extends WampEvent
{
    /**
     * @var string|Topic
     */
    private $id;

    /**
     * @var array
     */
    private $params;

    /**
     * @param ConnectionInterface $conn
     * @param string|Topic        $topic The topic to execute the call against
     * @param string              $id The unique ID of the RPC, required to respond to
     * @param array               $params Call parameters received from the client
     */
    public function __construct(ConnectionInterface $conn, $topic, $id, array $params)
    {
        parent::__construct($conn, $topic);

        $this->id = $id;
        $this->params = $params;
    }

    /**
     * @return string|Topic
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }
}
