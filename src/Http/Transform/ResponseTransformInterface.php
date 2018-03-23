<?php

declare(strict_types=1);

namespace CCT\Component\Rest\Http\Transform;

use CCT\Component\Rest\Http\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

interface ResponseTransformInterface
{
    /**
     * Executes the transformation of the response.
     *
     * @param ResponseInterface|Response $response
     *
     * @return void
     */
    public function transform(ResponseInterface $response);
}
