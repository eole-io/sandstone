<?php

namespace Eole\Sandstone\OAuth2\Security\Http\EntryPoint;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class NoEntryPoint implements AuthenticationEntryPointInterface
{
    /**
     * {@InheritDoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new Response('', Response::HTTP_UNAUTHORIZED);
    }
}
