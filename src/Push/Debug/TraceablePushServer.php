<?php

namespace Eole\Sandstone\Push\Debug;

use Eole\Sandstone\Push\PushServerInterface;

class TraceablePushServer implements TraceablePushServerInterface
{
    /**
     * @var PushServerInterface
     */
    private $pushServer;

    /**
     * @var string[]
     */
    private $sentMessages;

    /**
     * @param PushServerInterface $pushServer
     */
    public function __construct(PushServerInterface $pushServer)
    {
        $this->pushServer = $pushServer;
    }

    /**
     * {@InheritDoc}
     */
    public function send($message)
    {
        $this->pushServer->send($message);
        $this->sentMessages []= $message;
    }

    /**
     * @return string[]
     */
    public function getSentMessages()
    {
        return $this->sentMessages;
    }
}
