<?php

namespace Eole\Sandstone\Tests\Push;

use Symfony\Component\HttpFoundation\Request;
use Eole\Sandstone\Push\PushServerInterface;

class PushTest extends \PHPUnit_Framework_TestCase
{
    public function testOk()
    {
        $app = new App\AppRestApi([
            'debug' => true,
            'sandstone.push' => function () {
                $pushServerMock = $this->getMockForAbstractClass(PushServerInterface::class);

                return $pushServerMock;
            },
        ]);

        $app->boot();

        $result = $app->handle(Request::create('/api/articles', Request::METHOD_POST));

        var_dump($result->getContent());
    }
}
