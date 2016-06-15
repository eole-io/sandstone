<?php

namespace Eole\Sandstone\OAuth2\Test;

use League\OAuth2\Server\Exception\AccessDeniedException;
use League\OAuth2\Server\Entity\SessionEntity;
use League\OAuth2\Server\Entity\AccessTokenEntity;
use Eole\Sandstone\OAuth2\ResourceServer as BaseResourceServer;

class ResourceServerMock extends BaseResourceServer
{
    public function isValidRequest($headerOnly = true, $accessToken = null)
    {
        $split = explode('-', $accessToken);

        $start = array_shift($split);
        $username = implode('-', $split);

        if ('goodtoken' !== $start) {
            throw new AccessDeniedException();
        }

        $session = new SessionEntity($this);
        $session->setId($username);

        $this->accessToken = new AccessTokenEntity($this);
        $this->accessToken->setExpireTime(time() + 3600);
        $this->accessToken->setSession($session);

        return true;
    }
}
