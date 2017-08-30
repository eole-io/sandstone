<?php

namespace Eole\Sandstone\Tests\Integration\App;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Eole\Sandstone\Tests\Integration\App\ArticleCreatedEvent;

class PushMessageCommand extends Command
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        parent::__construct('sandstone:test:push');

        $this->dispatcher = $dispatcher;
    }

    /**
     * {@InheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Dispatch an event which should push an event.')
        ;
    }

    /**
     * {@InheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = 42;
        $title = 'Unicorns spotted in Alaska';
        $url = 'http://unicorn.com/articles/unicorns-spotted-alaska';

        $event = new ArticleCreatedEvent($id, $title, $url);

        $this->dispatcher->dispatch('article.created', $event);
    }
}
