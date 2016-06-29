<?php

namespace Eole\Sandstone\Tests\Push\App;

use Symfony\Component\HttpFoundation\JsonResponse;

class AppRestApi extends App
{
    public function __construct(array $values = array())
    {
        parent::__construct($values);

        $this->post('api/articles', function () {
            $articleId = 42;
            $title = 'Unicorns spotted in Alaska';
            $url = 'http://unicorn.com/articles/unicorns-spotted-alaska';

            $this['dispatcher']->dispatch('article.created', new ArticleCreatedEvent($title, $url));

            return new JsonResponse($articleId, 201);
        });

        $this->forwardEventToPushServer('article.created');
    }
}
