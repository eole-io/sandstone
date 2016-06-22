<?php

namespace Eole\Sandstone\OAuth2;

use League\OAuth2\Server\Storage;
use League\OAuth2\Server\AuthorizationServer as BaseAuthorizationServer;

class AuthorizationServer extends BaseAuthorizationServer
{
    /**
     * @var Grant\Password
     */
    private $passwordGrant;

    /**
     * @var Grant\RefreshToken
     */
    private $refreshTokenGrant;

    /**
     * @param Storage\SessionInterface $sessionStorage
     * @param Storage\AccessTokenInterface $accessTokenStorage
     * @param Storage\ClientInterface $clientStorage
     * @param Storage\ScopeInterface $scopeStorage
     * @param Storage\RefreshTokenInterface $refreshTokenStorage
     * @param Grant\Password $passwordGrant
     * @param Grant\RefreshToken $refreshTokenGrant
     */
    public function __construct(
        Storage\SessionInterface $sessionStorage,
        Storage\AccessTokenInterface $accessTokenStorage,
        Storage\ClientInterface $clientStorage,
        Storage\ScopeInterface $scopeStorage,
        Storage\RefreshTokenInterface $refreshTokenStorage,
        Grant\Password $passwordGrant,
        Grant\RefreshToken $refreshTokenGrant
    ) {
        parent::__construct();

        $this->sessionStorage = $sessionStorage;
        $this->accessTokenStorage = $accessTokenStorage;
        $this->clientStorage = $clientStorage;
        $this->scopeStorage = $scopeStorage;
        $this->refreshTokenStorage = $refreshTokenStorage;
        $this->passwordGrant = $passwordGrant;
        $this->refreshTokenGrant = $refreshTokenGrant;

        $this->initServer();
        $this->addPasswordGrant();
        $this->addRefreshTokenGrant();
    }

    /**
     * Init authorization server.
     */
    private function initServer()
    {
        $this
            ->setSessionStorage($this->sessionStorage)
            ->setAccessTokenStorage($this->accessTokenStorage)
            ->setClientStorage($this->clientStorage)
            ->setScopeStorage($this->scopeStorage)
        ;
    }

    /**
     * Allows authorization server to deliver an access token with username/password.
     */
    private function addPasswordGrant()
    {
        $this->addGrantType($this->passwordGrant);
    }

    /**
     * Allows authorization server to deliver a fresh access token with an old one.
     */
    private function addRefreshTokenGrant()
    {
        $this
            ->setRefreshTokenStorage($this->refreshTokenStorage)
            ->addGrantType($this->refreshTokenGrant)
        ;
    }
}
