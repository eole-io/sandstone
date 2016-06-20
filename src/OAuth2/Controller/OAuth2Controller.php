<?php

namespace Eole\Sandstone\OAuth2\Controller;

use Symfony\Component\HttpFoundation\Request;
use League\OAuth2\Server\Exception\OAuthException;
use League\OAuth2\Server\AuthorizationServer;

class OAuth2Controller
{
    /**
     * @var AuthorizationServer
     */
    private $authorizationServer;

    /**
     * @param AuthorizationServer $authorizationServer
     */
    public function __construct(AuthorizationServer $authorizationServer)
    {
        $this->authorizationServer = $authorizationServer;
    }

    /**
     * @param Request $request
     *
     * @return array like
     *      access_token:"...",
     *      token_type:"Bearer"
     *      expires_in:3600
     *      refresh_token:"..."
     *
     * @throws OAuthException
     */
    public function postAccessToken(Request $request)
    {
        $this->authorizationServer->setRequest($request);

        return $this->authorizationServer->issueAccessToken();
    }
}
