<?php

namespace Eole\Sandstone\OAuth2\Silex;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;

class OAuth2ControllerProvider implements ControllerProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->post('/access-token', 'sandstone.oauth.controller:postAccessToken');

        return $controllers;
    }
}
