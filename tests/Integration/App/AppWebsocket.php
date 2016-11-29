<?php

namespace Eole\Sandstone\Tests\Integration\App;

class AppWebsocket extends App
{
    public function __construct(array $values = array())
    {
        parent::__construct($values);

        $this->topic('articles', function ($topicPattern, $arguments) {
            $channelName = $arguments['channel'];

            return new ArticleTopic($topicPattern, $channelName);
        });
    }
}
