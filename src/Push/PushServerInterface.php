<?php

namespace Eole\Sandstone\Push;

interface PushServerInterface
{
    /**
     * Send a message to the Push server.
     *
     * @param string $message
     */
    public function send($message);
}
