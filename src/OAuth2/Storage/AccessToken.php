<?php

namespace Eole\Sandstone\OAuth2\Storage;

use League\OAuth2\Server\Entity\AccessTokenEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Entity\SessionEntity;
use League\OAuth2\Server\Storage\AccessTokenInterface;
use League\OAuth2\Server\Storage\AbstractStorage;

class AccessToken extends AbstractStorage implements AccessTokenInterface
{
    /**
     * @var string
     */
    private $accessTokensDir;

    /**
     * @param string $accessTokensDir
     */
    public function __construct($accessTokensDir)
    {
        $this->accessTokensDir = $accessTokensDir;
    }

    /**
     * {@InheritDoc}
     */
    public function get($token)
    {
        if (!file_exists($this->accessTokensDir.'/'.$token)) {
            return null;
        }

        $tokenContent = explode('-', file_get_contents($this->accessTokensDir.'/'.$token));
        $sessionId = $tokenContent[0];
        $expireTime = $tokenContent[1];

        $session = new SessionEntity($this->server);
        $session->setId($sessionId);

        $accessToken = new AccessTokenEntity($this->server);
        $accessToken->setId($token);
        $accessToken->setExpireTime(intval($expireTime));
        $accessToken->setSession($session);

        return $accessToken;
    }

    /**
     * {@InheritDoc}
     */
    public function getScopes(AccessTokenEntity $token)
    {
        throw new \Eole\Sandstone\OAuth2\Exception\NotImplementedException();
    }

    /**
     * {@InheritDoc}
     */
    public function create($token, $expireTime, $sessionId)
    {
        file_put_contents($this->accessTokensDir.'/'.$token, $sessionId.'-'.$expireTime);
    }

    /**
     * {@InheritDoc}
     */
    public function associateScope(AccessTokenEntity $token, ScopeEntity $scope)
    {
        $token->associateScope($scope);
    }

    /**
     * {@InheritDoc}
     */
    public function delete(AccessTokenEntity $token)
    {
        if (file_exists($this->accessTokensDir.'/'.$token->getId())) {
            unlink($this->accessTokensDir.'/'.$token->getId());
        }
    }
}
