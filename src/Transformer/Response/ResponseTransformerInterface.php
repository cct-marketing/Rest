<?php

namespace CCT\Component\Rest\Transformer\Response;

use CCT\Component\Rest\Http\ResponseInterface;
use CCT\Component\Rest\Serializer\ContextInterface;
use Symfony\Component\HttpFoundation\Response;

interface ResponseTransformerInterface
{
    /**
     * Executes the transformation of the response.
     *
     * @param ResponseInterface|Response $response
     * @param ContextInterface|null $context
     *
     * @return void
     */
    public function transform(ResponseInterface $response, ContextInterface $context = null);

    /**
     * Checks if the response sent is supported to executes the transformation.
     *
     * @param ResponseInterface|Response $response
     *
     * @return bool
     */
    public function supports(ResponseInterface $response): bool;
}
