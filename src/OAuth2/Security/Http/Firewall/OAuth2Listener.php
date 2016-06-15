<?php

namespace Eole\Sandstone\OAuth2\Security\Http\Firewall;

use League\OAuth2\Server\ResourceServer;
use League\OAuth2\Server\Exception\InvalidRequestException;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Eole\Sandstone\OAuth2\Security\Authentication\Token\OAuth2Token;

class OAuth2Listener implements ListenerInterface
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var AuthenticationManagerInterface
     */
    protected $authenticationManager;

    /**
     * @var ResourceServer
     */
    private $resourceServer;

    /**
     * @param TokenStorageInterface $tokenStorage
     * @param AuthenticationManagerInterface $authenticationManager
     * @param ResourceServer $resourceServer
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationManagerInterface $authenticationManager,
        ResourceServer $resourceServer
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->resourceServer = $resourceServer;
    }

    /**
     * {@InheritDoc}
     */
    public function handle(GetResponseEvent $event)
    {
        $this->resourceServer->setRequest($event->getRequest());

        try {
            $tokenData = $this->resourceServer->determineAccessToken(true);
            $authenticatedToken = $this->authenticationManager->authenticate(new OAuth2Token($tokenData));
            $this->tokenStorage->setToken($authenticatedToken);
        } catch (InvalidRequestException $e) {
            // noop, allow requests without token.
        }
    }
}
