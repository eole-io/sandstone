<?php

namespace Eole\Sandstone\Tests\Integration\App;

use Symfony\Component\Console\Application as ConsoleApplication;
use Eole\Sandstone\Tests\Integration\App\App as SilexApplication;

class Console extends ConsoleApplication
{
    /**
     * @var SilexApplication
     */
    private $silexApplication;

    /**
     * Console application constructor.
     *
     * @param SilexApplication $silexApplication
     */
    public function __construct(SilexApplication $silexApplication)
    {
        parent::__construct('My Sandstone application');

        $this->silexApplication = $silexApplication;
        $this->silexApplication->boot();

        $this->addCommands([
            new PushMessageCommand($this->silexApplication['dispatcher']),
        ]);
    }
}
