<?php

require_once '../vendor/autoload.php';
require_once 'App.php';

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\JsonResponse;

$app = new App();

// Creating an api endpoint at POST api/articles
$app->post('api/articles', function () use ($app) {
    // Persisting article...
    $articleId = 42;

    $event = new Event();

    $event->id = $articleId;
    $event->title = 'Unicorns spotted in Alaska';
    $event->url = 'http://unicorn.com/articles/unicorns-spotted-alaska';

    // Dispatch an event on article creation
    $app['dispatcher']->dispatch('article.created', $event);

    return new JsonResponse($articleId, 201);
});

// Send all 'article.created' events to push server
$app->forwardEventToPushServer('article.created');

$app->run();
