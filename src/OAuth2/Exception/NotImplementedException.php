<?php

namespace Eole\Sandstone\OAuth2\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class NotImplementedException extends HttpException
{
    public function __construct(\Exception $previous = null)
    {
        parent::__construct(
            Response::HTTP_NOT_IMPLEMENTED,
            'This part of OAuth2 implementation is not yet implemented.',
            $previous
        );
    }
}
