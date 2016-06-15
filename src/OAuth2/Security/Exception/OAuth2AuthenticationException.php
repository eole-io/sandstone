<?php

namespace Eole\Sandstone\OAuth2\Security\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class OAuth2AuthenticationException extends AuthenticationException
{
    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return 'The OAuth2 authentication has failed.';
    }
}
