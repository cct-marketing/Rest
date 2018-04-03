<?php

namespace CCT\Component\Rest\Transformer\Response;

use CCT\Component\Rest\Http\Response;
use CCT\Component\Rest\Http\ResponseInterface;
use CCT\Component\Rest\Serializer\ContextInterface;

class ObjectTransformer extends AbstractSerializerResponseTransformer
{
    /**
     * @param ResponseInterface|Response $response
     *
     * {@inheritdoc}
     */
    public function transform(ResponseInterface $response, ContextInterface $context = null): void
    {
        $data = $this->serializer->deserialize(
            $response->getContent(),
            $this->class,
            'json',
            $context ?? $this->context
        );

        $response->setData($data);
    }

    /**
     * @param ResponseInterface|Response $response
     *
     * {@inheritdoc}
     */
    public function supports(ResponseInterface $response): bool
    {
        return
            $response->isSuccessful()
            && !empty($response->getData())
            && false === $this->isSequential($response->getData());
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    protected function isSequential(array $data): bool
    {
        return array_keys($data) === range(0, count($data) - 1);
    }
}
