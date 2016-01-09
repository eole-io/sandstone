<?php

namespace Eole\Sandstone\Push\Debug\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Eole\Sandstone\Push\Debug\TraceablePushServerInterface;
use Eole\Sandstone\Push\PushServerInterface;

class PushServerDataCollector extends DataCollector
{
    /**
     * @var string
     */
    const NAME = 'sandstone.push_server';

    /**
     * @var PushServerInterface
     */
    private $pushServer;

    /**
     * @param PushServerInterface $pushServer
     */
    public function __construct(PushServerInterface $pushServer)
    {
        $this->pushServer = $pushServer;
        $this->updateMessages(array());
    }

    /**
     * {@InheritDoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        if ($this->pushServer instanceof TraceablePushServerInterface) {
            $this->updateMessages($this->pushServer->getSentMessages());
        }
    }

    /**
     * Update data collector data with messages.
     *
     * @param string[] $messages
     */
    private function updateMessages(array $messages)
    {
        $this->data = array(
            'messages_size' => 0,
            'messages_count' => 0,
            'messages' => array(),
        );

        foreach ($messages as $message) {
            $messageSize = strlen($message);

            $this->data['messages_size'] += $messageSize;
            $this->data['messages_count']++;

            $this->data['messages'] []= array(
                'content' => $message,
                'size' => $messageSize,
                'decoded' => unserialize($message),
            );
        }
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * {@InheritDoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
