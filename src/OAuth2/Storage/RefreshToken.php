<?php

namespace Eole\Sandstone\OAuth2\Storage;

use League\OAuth2\Server\Entity\AccessTokenEntity;
use League\OAuth2\Server\Entity\RefreshTokenEntity;
use League\OAuth2\Server\Storage\RefreshTokenInterface;
use League\OAuth2\Server\Storage\AbstractStorage;

class RefreshToken extends AbstractStorage implements RefreshTokenInterface
{
    /**
     * @var string
     */
    private $refreshTokensDir;

    /**
     * @param string $refreshTokensDir
     */
    public function __construct($refreshTokensDir)
    {
        $this->refreshTokensDir = $refreshTokensDir;
    }

    /**
     * {@InheritDoc}
     */
    public function get($token)
    {
        if (!file_exists($this->refreshTokensDir.'/'.$token)) {
            return null;
        }

        $tokenContent = explode('-', file_get_contents($this->refreshTokensDir.'/'.$token));
        $accessToken = $tokenContent[0];
        $expireTime = $tokenContent[1];

        $accessTokenEntity = new AccessTokenEntity($this->server);
        $accessTokenEntity->setId($accessToken);

        $refreshTokenEntity = new RefreshTokenEntity($this->server);
        $refreshTokenEntity->setId($token);
        $refreshTokenEntity->setExpireTime($expireTime);
        $refreshTokenEntity->setAccessToken($accessTokenEntity);

        return $refreshTokenEntity;
    }

    /**
     * {@InheritDoc}
     */
    public function create($token, $expireTime, $accessToken)
    {
        file_put_contents($this->refreshTokensDir.'/'.$token, $accessToken.'-'.$expireTime);
    }

    /**
     * {@InheritDoc}
     */
    public function delete(RefreshTokenEntity $token)
    {
        if (file_exists($this->refreshTokensDir.'/'.$token->getId())) {
            unlink($this->refreshTokensDir.'/'.$token->getId());
        }
    }
}
