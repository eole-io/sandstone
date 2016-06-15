<?php

namespace Eole\Sandstone\OAuth2\Controller;

use Symfony\Component\HttpFoundation\Request;
use League\OAuth2\Server\Exception\OAuthException;
use League\OAuth2\Server\AuthorizationServer;
use Alcalyn\SerializableApiResponse\ApiResponse;

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
     * @return ApiResponse
     */
    public function postAccessToken(Request $request)
    {
        $this->authorizationServer->setRequest($request);

        try {
            $token = $this->authorizationServer->issueAccessToken();

            return new ApiResponse($token);
        } catch (OAuthException $e) {
            return new ApiResponse(array(
                'type' => $e->errorType,
                'message' => $e->getMessage(),
            ), $e->httpStatusCode);
        }
    }
}
