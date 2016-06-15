<?php

namespace Eole\Sandstone\OAuth2\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class OAuth2Token extends AbstractToken
{
    /**
     * @var string
     */
    private $tokenData;

    /**
     * @param string $tokenData
     */
    public function __construct($tokenData)
    {
        parent::__construct();

        $this->tokenData = $tokenData;
    }

    /**
     * @return string
     */
    public function getTokenData()
    {
        return $this->tokenData;
    }

    /**
     * @param string $tokenData
     *
     * @return self
     */
    public function setTokenData($tokenData)
    {
        $this->tokenData = $tokenData;

        return $this;
    }

    /**
     * {@InheritDoc}
     */
    public function getCredentials()
    {
        return '';
    }
}
