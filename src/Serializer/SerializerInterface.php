<?php

namespace CCT\Component\Rest\Serializer;

interface SerializerInterface
{
    /**
     * Serializes the given data to the specified output format.
     *
     * @param object|array|scalar $data
     * @param string $format
     * @param ContextInterface|Context $context
     *
     * @return string
     */
    public function serialize($data, $format, ContextInterface $context = null);

    /**
     * Deserializes the given data to the specified type.
     *
     * @param string $data
     * @param string $type
     * @param string $format
     * @param ContextInterface|Context $context
     *
     * @return object|array|scalar
     */
    public function deserialize($data, $type, $format, ContextInterface $context = null);
}
