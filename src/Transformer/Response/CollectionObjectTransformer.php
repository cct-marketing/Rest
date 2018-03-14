<?php

namespace CCT\Component\Rest\Transformer\Response;

use CCT\Component\Rest\Model\Response\ContentCollection;
use CCT\Component\Rest\Transformer\SerializerTransformer;
use CCT\Component\Rest\Http\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class CollectionObjectTransformer extends SerializerTransformer
{
    /**
     * @param ResponseInterface|Response $response
     *
     * {@inheritdoc}
     */
    public function transform(ResponseInterface $response)
    {
        $data = $response->getData();

        foreach ($data['data'] as $k => $object) {
            $data['data'][$k] = $this->serializer->deserialize(
                json_encode($object),
                $this->class,
                'json',
                $this->context
            );
        }

        $data = new ContentCollection($data);
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

        return (
            is_array($data)
            && isset($data['data'])
            && isset($data['total'])
            && $response->isSuccessful()
            && !empty($data)
        );
    }
}
