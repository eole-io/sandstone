<?php

namespace Eole\Sandstone\Push;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\Event;
use Eole\Sandstone\Push\PushServerInterface;

class EventForwarder
{
    /**
     * @var PushServerInterface
     */
    private $pushServer;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var EventSerializer
     */
    private $eventSerializer;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * @param PushServerInterface $pushServer
     * @param EventDispatcherInterface $dispatcher
     * @param EventSerializer $eventSerializer
     * @param bool $enabled
     */
    public function __construct(
        PushServerInterface $pushServer,
        EventDispatcherInterface $dispatcher,
        EventSerializer $eventSerializer,
        $enabled = true
    ) {
        $this->pushServer = $pushServer;
        $this->dispatcher = $dispatcher;
        $this->eventSerializer = $eventSerializer;
        $this->enabled = $enabled;
    }

    /**
     * Forward an Event to Push Server.
     *
     * @param Event $event
     * @param string $name
     */
    public function forwardEvent(Event $event, $name)
    {
        if (!$this->enabled) {
            return;
        }

        $this->pushServer->send($this->eventSerializer->serializeEvent($name, $event));
    }

    /**
     * Automatically forward RestApi events to push server.
     *
     * @param string|string[] $eventNames Can be an event name or an array of event names.
     *
     * @return self
     */
    public function forwardAllEvents($eventNames)
    {
        if (!$this->enabled) {
            return $this;
        }

        if (!is_array($eventNames)) {
            $eventNames = array($eventNames);
        }

        foreach ($eventNames as $eventName) {
            $this->dispatcher->addListener(
                $eventName,
                array($this, 'forwardEvent')
            );
        }

        return $this;
    }
}
