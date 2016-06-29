<?php

namespace Eole\Sandstone\Tests\Push\App;

use Symfony\Component\EventDispatcher\Event;

class ArticleCreatedEvent extends Event
{
    public $id;
    public $title;
    public $url;
}
