<?php

namespace Eole\Sandstone\Websocket\Event;

use Ratchet\Wamp\Topic;
use Ratchet\ConnectionInterface;

class PublishEvent extends WampEvent
{
    /**
     * @var string
     */
    private $event;

    /**
     * @var array
     */
    private $exclude;

    /**
     * @var array
     */
    private $eligible;

    /**
     * @param ConnectionInterface $conn
     * @param string|Topic        $topic The topic the user has attempted to publish to
     * @param string              $event Payload of the publish
     * @param array               $exclude A list of session IDs the message should be excluded from (blacklist)
     * @param array               $eligible A list of session Ids the message should be send to (whitelist)
     */
    public function __construct(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible)
    {
        parent::__construct($conn, $topic);

        $this->event = $event;
        $this->exclude = $exclude;
        $this->eligible = $eligible;
    }

    /**
     * @return string
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return array
     */
    public function getExclude()
    {
        return $this->exclude;
    }

    /**
     * @return array
     */
    public function getEligible()
    {
        return $this->eligible;
    }
}
