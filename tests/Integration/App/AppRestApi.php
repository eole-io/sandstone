<?php

namespace Eole\Sandstone\Tests\Integration\App;

use Symfony\Component\HttpFoundation\JsonResponse;

class AppRestApi extends App
{
    public function __construct(array $values = array())
    {
        parent::__construct($values);

        $this->post('api/articles', function () {
            $id = 42;
            $title = 'Unicorns spotted in Alaska';
            $url = 'http://unicorn.com/articles/unicorns-spotted-alaska';

            $this['dispatcher']->dispatch('article.created', new ArticleCreatedEvent($id, $title, $url));

            return new JsonResponse($id, 201);
        });

        $this->forwardEventToPushServer('article.created');
    }
}
