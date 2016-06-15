<?php

namespace Eole\Sandstone\OAuth2;

use League\OAuth2\Server\ResourceServer as BaseResourceServer;

class ResourceServer extends BaseResourceServer
{
    /**
     * @param Storage\Session $sessionStorage
     * @param Storage\AccessToken $accessTokenStorage
     * @param Storage\Client $clientStorage
     * @param Storage\Scope $scopeStorage
     */
    public function __construct(
        Storage\Session $sessionStorage,
        Storage\AccessToken $accessTokenStorage,
        Storage\Client $clientStorage,
        Storage\Scope $scopeStorage
    ) {
        parent::__construct(
            $sessionStorage,
            $accessTokenStorage,
            $clientStorage,
            $scopeStorage
        );
    }
}
