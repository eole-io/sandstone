<?php

namespace Eole\Sandstone\Push\Debug;

use Eole\Sandstone\Push\PushServerInterface;

interface TraceablePushServerInterface extends PushServerInterface
{
    /**
     * Returns sent messages during this request.
     *
     * @return string[]
     */
    public function getSentMessages();
}
