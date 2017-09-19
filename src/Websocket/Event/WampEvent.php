<?php

namespace Eole\Sandstone\Websocket\Event;

use Ratchet\Wamp\Topic;
use Ratchet\ConnectionInterface;

class WampEvent extends ConnectionEvent
{
    /**
     * @param ConnectionInterface $conn
     * @param string|Topic        $topic The topic the user has attempted to publish to
     */
    public function __construct(ConnectionInterface $conn, $topic)
    {
        parent::__construct($conn);

        $this->topic = $topic;
    }

    /**
     * @return string|Topic
     */
    public function getTopic()
    {
        return $this->topic;
    }
}
