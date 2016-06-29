<?php

namespace Eole\Sandstone\Push\Bridge\ZMQ;

use ZMQSocket;
use Eole\Sandstone\Push\PushServerInterface;

class ZMQPushServer implements PushServerInterface
{
    /**
     * @var ZMQSocket
     */
    private $zmqSocket;

    /**
     * @param ZMQSocket $zmqSocket
     */
    public function __construct(ZMQSocket $zmqSocket)
    {
        $this->zmqSocket = $zmqSocket;
    }

    /**
     * {@InheritDoc}
     */
    public function send($message)
    {
        $this->zmqSocket->send($message);
    }
}
