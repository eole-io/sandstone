<?php

namespace Eole\Sandstone\OAuth2\Security\Authentication\Provider;

use League\OAuth2\Server\Exception\AccessDeniedException;
use League\OAuth2\Server\ResourceServer;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Eole\Sandstone\OAuth2\Security\Exception\OAuth2AuthenticationException;
use Eole\Sandstone\OAuth2\Security\Authentication\Token\OAuth2Token;

class OAuth2Provider implements AuthenticationProviderInterface
{
    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * @var UserCheckerInterface
     */
    private $userChecker;

    /**
     * @var ResourceServer
     */
    private $resourceServer;

    /**
     * @param UserProviderInterface $userProvider
     * @param UserCheckerInterface $userChecker
     * @param ResourceServer $resourceServer
     */
    public function __construct(
        UserProviderInterface $userProvider,
        UserCheckerInterface $userChecker,
        ResourceServer $resourceServer
    ) {
        $this->userProvider = $userProvider;
        $this->userChecker = $userChecker;
        $this->resourceServer = $resourceServer;
    }

    /**
     * {@InheritDoc}
     */
    public function authenticate(TokenInterface $token)
    {
        try {
            $this->resourceServer->isValidRequest(true, $token->getTokenData());
        } catch (AccessDeniedException $e) {
            throw new OAuth2AuthenticationException('OAuth2 token expired or invalid.');
        }

        $username = $this->resourceServer->getAccessToken()->getSession()->getId();
        $user = $this->userProvider->loadUserByUsername($username);
        $isUser = $user instanceof UserInterface;

        if (!$isUser) {
            throw new OAuth2AuthenticationException('User not found.');
        }

        $this->userChecker->checkPreAuth($user);
        $this->userChecker->checkPostAuth($user);

        $authenticatedToken = new OAuth2Token($token->getTokenData());
        $authenticatedToken->setUser($user);

        return $authenticatedToken;
    }

    /**
     * {@InheritDoc}
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof OAuth2Token;
    }
}
