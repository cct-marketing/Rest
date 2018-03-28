<?php

declare(strict_types=1);

namespace CCT\Component\Rest\Http\Transform;

use CCT\Component\Rest\Http\ResponseInterface;
use CCT\Component\Rest\Serializer\ContextInterface;

interface ResponseTransformInterface
{
    /**
     * Executes the transformation of the response.
     *
     * @param ResponseInterface $response
     * @param ContextInterface|null $context
     *
     * @return mixed
     */
    public function transform(ResponseInterface $response, ContextInterface $context = null);
}
