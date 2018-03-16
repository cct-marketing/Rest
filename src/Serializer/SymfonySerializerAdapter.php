<?php

namespace CCT\Component\Rest\Serializer;

use CCT\Component\Rest\Serializer\Context\Context;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface as SymfonySerializerInterface;

/**
 * Adapter to plug the Symfony serializer into the FOSRestBundle Serializer API.
 */
class SymfonySerializerAdapter implements SerializerInterface
{
    /**
     * @var SymfonySerializerInterface|Serializer
     */
    private $serializer;

    public function __construct(SymfonySerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Serializes the given data to the specified output format.
     *
     * @param object|array|scalar $data
     * @param string $format
     * @param ContextInterface|null|Context $context
     *
     * @return string
     */
    public function serialize($data, $format, ContextInterface $context = null)
    {
        $newContext = $this->convertContext($context);
        $newContext['serializeNull'] = $context->getSerializeNull();

        return $this->serializer->serialize($data, $format, $newContext);
    }

    /**
     * @param string $data
     * @param string $type
     * @param string $format
     * @param ContextInterface|null|Context $context
     *
     * @return object
     */
    public function deserialize($data, $type, $format, ContextInterface $context = null)
    {
        $newContext = $this->convertContext($context);

        return $this->serializer->deserialize($data, $type, $format, $newContext);
    }

    /**
     * @param Context $context
     *
     * @return array
     */
    private function convertContext(Context $context)
    {
        $newContext = array();
        foreach ($context->getAttributes() as $key => $value) {
            $newContext[$key] = $value;
        }

        if (null !== $context->getGroups()) {
            $newContext['groups'] = $context->getGroups();
        }
        $newContext['version'] = $context->getVersion();
        $newContext['maxDepth'] = $context->getMaxDepth(false);
        $newContext['enable_max_depth'] = $context->isMaxDepthEnabled();

        return $newContext;
    }

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
    public function toArray($data, ContextInterface $context = null)
    {
        $newContext = $this->convertContext($context);

        $this->serializer->normalize($data, null, $context);
    }

    /**
     * Restores objects from an array structure.
     *
     * @param array $data
     * @param string $type
     * @param ContextInterface|null $context
     *
     * @return mixed this returns whatever the passed type is, typically an object or an array of objects
     */
    public function fromArray(array $data, $type, ContextInterface $context = null)
    {
        $newContext = $this->convertContext($context);

        $this->serializer->denormalize($data, $type, null, $newContext);
    }
}
