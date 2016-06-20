<?php

namespace Eole\Sandstone\OAuth2\Silex;

use League\OAuth2\Server\Exception\OAuthException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;

class OAuth2JsonControllerProvider implements ControllerProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->post('/access-token', function (Request $request) use ($app) {
            try {
                $token = $app['sandstone.oauth.controller']->postAccessToken($request);

                return new JsonResponse($token);
            } catch (OAuthException $e) {
                throw new HttpException($e->httpStatusCode, $e->errorType.': '.$e->getMessage(), $e);
            }
        });

        return $controllers;
    }
}
