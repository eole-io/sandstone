<?php

namespace Eole\Sandstone\Logger;

use Psr\Log\AbstractLogger;

class EchoLogger extends AbstractLogger
{
    /**
     * {@InheritDoc}
     */
    public function log($level, $message, array $context = array())
    {
        echo sprintf('[%s] %s %s', $level, $message, json_encode($context, JSON_UNESCAPED_SLASHES)).PHP_EOL;
    }
}