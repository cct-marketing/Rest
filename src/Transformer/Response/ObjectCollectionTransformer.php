<?php

namespace CCT\Component\Rest\Transformer\Response;

use CCT\Component\Rest\Http\ResponseInterface;
use CCT\Component\Rest\Serializer\ContextInterface;
use Symfony\Component\HttpFoundation\Response;

class ObjectCollectionTransformer extends AbstractSerializerResponseTransformer
{
    /**
     * @var array|null
     */
    protected $mappingKeys;

    /**
     * @param ResponseInterface|Response $response
     *
     * {@inheritdoc}
     */
    public function transform(ResponseInterface $response, ContextInterface $context = null): void
    {
        $data = $response->getData();

        if (!is_array($this->mappingKeys) || \count($this->mappingKeys) === 0) {
            $response->setData(
                $this->transformArrayPartial($data, $context)
            );
            return;
        }

        $mappedData = $this->map($response->getData());
        $transformedData = [];

        foreach ($mappedData as $key => $transformingData) {
            $transformedData[$key] = $this->transformArrayPartial($transformingData, $context);
        }

        $response->setData(array_merge($data, $transformedData));
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
            && $this->isArrayAndNotEmpty($data)
            && $this->mappingKeysExist($data)
            && $this->isDataSequential($this->map($data))
        );
    }

    /**
     * @param array $transformingData
     * @param ContextInterface $context
     *
     * @return array
     */
    protected function transformArrayPartial(array $transformingData, ContextInterface $context = null): array
    {
        $transformedData = [];

        foreach ($transformingData as $key => $object) {
            $transformedData[$key] = $this->serializer->deserialize(
                json_encode($object),
                $this->class,
                'json',
                $context ?? $this->context
            );
        }

        return $transformedData;
    }

    /**
     * Check if $data is an array and not empty
     *
     * @param mixed $data
     *
     * @return bool
     */
    protected function isArrayAndNotEmpty($data): bool
    {
        return \is_array($data) && !empty($data);
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

        $mappingKeys = $this->mappingKeys;

        return
            array_filter(
                $data,
                function ($key) use ($mappingKeys) {
                    return \in_array($key, $mappingKeys, true);
                },
                ARRAY_FILTER_USE_KEY
            );
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    protected function mappingKeysExist(array $data): bool
    {
        if (null === $this->mappingKeys) {
            return true;
        }

        return (bool)\count(array_intersect($this->mappingKeys, array_keys($data))) > 0;
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
    public function setMappingKeys(array $mappingKeys = null): void
    {
        $this->mappingKeys = $mappingKeys;
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    protected function isDataSequential(array $data): bool
    {
        if (null === $this->mappingKeys) {
            return $this->isSequential($data);
        }

        foreach ($data as $sequentialArray) {
            if (false === $this->isSequential($sequentialArray)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    protected function isSequential(array $data): bool
    {
        return array_keys($data) === range(0, \count($data) - 1);
    }
}
