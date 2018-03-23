<?php

namespace CCT\Component\Rest\Transformer\Response;

use CCT\Component\Rest\Http\Response;
use CCT\Component\Rest\Http\ResponseInterface;

class ObjectTransformer extends AbstractSerializerResponseTransformer
{
    /**
     * @param ResponseInterface|Response $response
     *
     * {@inheritdoc}
     */
    public function transform(ResponseInterface $response)
    {
        $data = $this->serializer->deserialize(
            $response->getContent(),
            $this->class,
            'json',
            $this->context
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
        $data = $response->getData();

        return
            false === isset($data['data'])
            && $response->isSuccessful()
            && !empty($data)
        ;
    }
}
