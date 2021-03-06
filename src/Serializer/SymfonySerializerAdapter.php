<?php

namespace CCT\Component\Rest\Serializer;

use CCT\Component\Rest\Serializer\Context\Context;
use Symfony\Component\Serializer\Serializer;

/**
 * Adapter to plug the Symfony serializer into the FOSRestBundle Serializer API.
 */
class SymfonySerializerAdapter implements SerializerInterface
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * SymfonySerializerAdapter constructor.
     *
     * @param Serializer $serializer
     */
    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Serializes the given data to the specified output format.
     *
     * @param object|array $data
     * @param string $format
     * @param ContextInterface|null|Context $context
     *
     * @return string
     */
    public function serialize($data, $format, ContextInterface $context = null): ?string
    {
        $newContext = $this->convertContext($context);
        if (null !== $context) {
            $newContext['serializeNull'] = $context->getSerializeNull();
        }

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
     * @param ContextInterface $context
     *
     * @return array|null
     */
    private function convertContext(ContextInterface $context = null): ?array
    {
        if (null === $context) {
            return null;
        }

        $newContext = array();
        foreach ($context->getAttributes() as $key => $value) {
            $newContext[$key] = $value;
        }

        if (null !== $context->getGroups()) {
            $newContext['groups'] = $context->getGroups();
        }
        $newContext['version'] = $context->getVersion();
        $newContext['maxDepth'] = $context->getMaxDepth();
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
     * @throws \Symfony\Component\Serializer\Exception\LogicException
     * @throws \Symfony\Component\Serializer\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Serializer\Exception\CircularReferenceException
     */
    public function toArray($data, ContextInterface $context = null): array
    {
        $newContext = $this->convertContext($context);

        return $this->serializer->normalize($data, null, $newContext);
    }

    /**
     * Restores objects from an array structure.
     *
     * @param array $data
     * @param string $type
     * @param ContextInterface|null $context
     *
     * @return mixed this returns whatever the passed type is, typically an object or an array of objects
     * @throws \Symfony\Component\Serializer\Exception\UnexpectedValueException
     * @throws \Symfony\Component\Serializer\Exception\RuntimeException
     * @throws \Symfony\Component\Serializer\Exception\NotNormalizableValueException
     * @throws \Symfony\Component\Serializer\Exception\LogicException
     * @throws \Symfony\Component\Serializer\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Serializer\Exception\ExtraAttributesException
     * @throws \Symfony\Component\Serializer\Exception\BadMethodCallException
     */
    public function fromArray(array $data, $type, ContextInterface $context = null)
    {
        $newContext = $this->convertContext($context);

        return $this->serializer->denormalize($data, $type, null, $newContext);
    }
}
