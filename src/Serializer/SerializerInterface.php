<?php

namespace CCT\Component\Rest\Serializer;

use CCT\Component\Rest\Serializer\Context\Context;

interface SerializerInterface
{
    /**
     * Serializes the given data to the specified output format.
     *
     * @param object|array $data
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
     * @return object|array
     */
    public function deserialize($data, $type, $format, ContextInterface $context = null);

    /**
     * Converts objects to an array structure.
     *
     * This is useful when the data needs to be passed on to other methods which expect array data.
     *
     * @param mixed $data anything that converts to an array, typically an object or an array of objects
     * @param ContextInterface|null $context
     *
     * @return array
     */
    public function toArray($data, ContextInterface $context = null);

    /**
     * Restores objects from an array structure.
     *
     * @param array $data
     * @param string $type
     * @param ContextInterface|null $context
     *
     * @return mixed this returns whatever the passed type is, typically an object or an array of objects
     */
    public function fromArray(array $data, $type, ContextInterface $context = null);
}
