<?php

namespace Eole\Sandstone\OAuth2\Storage;

use League\OAuth2\Server\Entity\SessionEntity;
use League\OAuth2\Server\Entity\AccessTokenEntity;
use League\OAuth2\Server\Entity\AuthCodeEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\SessionInterface;

class Session extends AbstractStorage implements SessionInterface
{
    /**
     * @var string
     */
    private $accessTokensDir;

    /**
     * @var string[]
     */
    private $scope;

    /**
     * @param string $accessTokensDir
     * @param string[] $scope
     */
    public function __construct($accessTokensDir, array $scope)
    {
        $this->accessTokensDir = $accessTokensDir;
        $this->scope = $scope;
    }

    /**
     * {@InheritDoc}
     */
    public function getByAccessToken(AccessTokenEntity $accessToken)
    {
        if (!file_exists($this->accessTokensDir.'/'.$accessToken->getId())) {
            return null;
        }

        $tokenContent = explode('-', file_get_contents($this->accessTokensDir.'/'.$accessToken->getId()));
        $sessionId = $tokenContent[0];

        $session = new SessionEntity($this->server);
        $session->setId($sessionId);

        return $session;
    }

    /**
     * {@InheritDoc}
     */
    public function getByAuthCode(AuthCodeEntity $authCode)
    {
        throw new \Eole\Sandstone\OAuth2\Exception\NotImplementedException();
    }

    /**
     * {@InheritDoc}
     */
    public function getScopes(SessionEntity $session)
    {
        $scope = new ScopeEntity($this->server);

        $scope->hydrate($this->scope);

        return array($scope);
    }

    /**
     * {@InheritDoc}
     */
    public function create($ownerType, $ownerId, $clientId, $clientRedirectUri = null)
    {
        return $ownerId;
    }

    /**
     * {@InheritDoc}
     */
    public function associateScope(SessionEntity $session, ScopeEntity $scope)
    {
        $session->associateScope($scope);
    }
}
