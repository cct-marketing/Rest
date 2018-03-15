<?php

namespace CCT\Component\Rest\Transformer\Response;

use CCT\Component\Rest\Model\Response\ContentCollection;
use CCT\Component\Rest\Transformer\AbstractSerializerTransformer;
use CCT\Component\Rest\Http\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class CollectionObjectTransformer extends AbstractSerializerTransformer
{
    protected $mappingKeys = null;

    /**
     * @param ResponseInterface|Response $response
     *
     * {@inheritdoc}
     */
    public function transform(ResponseInterface $response)
    {
        $data = $this->map($response->getData());
        foreach ($data as $k => $object) {
            $data[$k] = $this->serializer->deserialize(
                json_encode($object),
                $this->class,
                'json',
                $this->context
            );
        }

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
            $response->isSuccessful()
            && is_array($data)
            && !empty($data)
            && $this->mappingKeysExist($data)
            && $this->isSequential($this->map($data))
        );
    }

    /**
     * Extract collection from response array based on mapping key.
     * Supports single level only
     * If no mapping key returns full data
     *
     * @param $data
     *
     * @return array
     */
    protected function map(array $data): array
    {
        if (null === $this->mappingKeys) {
            return $data;
        }

        foreach ($this->mappingKeys as $key) {
            if (key_exists($key, $data)) {
                return $data;
            }
        }
        return [];
    }

    protected function mappingKeysExist(array $data): bool
    {
        if (null === $this->mappingKeys) {
            return true;
        }

        return (bool) count(array_intersect($this->mappingKeys, array_flip($data))) > 0;
    }

    /**
     * @return array|null
     */
    public function getMappingKeys(): ?array
    {
        return $this->mappingKeys;
    }

    /**
     * @param null|array $mappingKeys
     */
    public function setMappingKeys(array $mappingKeys = null)
    {
        $this->mappingKeys = $mappingKeys;
    }

    public function isSequential($data)
    {
        return array_keys($data) === range(0, count($data) - 1);
    }
}
