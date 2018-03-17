<?php

namespace CCT\Component\Rest\Serializer;

use CCT\Component\Rest\Serializer\Context\Context;
use JMS\Serializer\Context as JMSContext;
use JMS\Serializer\ContextFactory\DeserializationContextFactoryInterface;
use JMS\Serializer\ContextFactory\SerializationContextFactoryInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerInterface as JMSSerializerInterface;

/**
 * Adapter to plug the JMS serializer into the FOSRestBundle Serializer API.
 */
class JMSSerializerAdapter implements SerializerInterface
{
    /**
     * @internal
     */
    const SERIALIZATION = 0;
    /**
     * @internal
     */
    const DESERIALIZATION = 1;

    /**
     * @var JMSSerializerInterface|Serializer
     */
    private $serializer;

    /**
     * @var SerializationContextFactoryInterface
     */
    private $serializationContextFactory;

    /**
     * @var DeserializationContextFactoryInterface
     */
    private $deserializationContextFactory;

    public function __construct(
        JMSSerializerInterface $serializer,
        SerializationContextFactoryInterface $serializationContextFactory = null,
        DeserializationContextFactoryInterface $deserializationContextFactory = null
    ) {
        $this->serializer = $serializer;
        $this->serializationContextFactory = $serializationContextFactory;
        $this->deserializationContextFactory = $deserializationContextFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize($data, $format, ContextInterface $context = null)
    {
        $context = $this->convertContext($context, self::SERIALIZATION);

        return $this->serializer->serialize($data, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function deserialize($data, $type, $format, ContextInterface $context = null)
    {
        $context = $this->convertContext($context, self::DESERIALIZATION);

        return $this->serializer->deserialize($data, $type, $format, $context);
    }

    /**
     * @param ContextInterface|Context $context
     * @param int $direction {@see self} constants
     *
     * @return JMSContext|DeserializationContext|SerializationContext
     */
    private function convertContext(ContextInterface $context = null, $direction)
    {
        if (self::SERIALIZATION === $direction) {
            $jmsContext = $this->serializationContextFactory
                ? $this->serializationContextFactory->createSerializationContext()
                : SerializationContext::create();
        } else {
            $jmsContext = $this->deserializationContextFactory
                ? $this->deserializationContextFactory->createDeserializationContext()
                : DeserializationContext::create();
            $maxDepth = $context->getMaxDepth();
            if (null !== $maxDepth) {
                for ($i = 0; $i < $maxDepth; ++$i) {
                    $jmsContext->increaseDepth();
                }
            }
        }

        return $this->mapContextAttributes($context, $jmsContext);
    }

    /**
     * Set jms context attributes from context
     *
     * @param ContextInterface|Context $context
     * @param JMSContext $jmsContext
     *
     * @return JMSContext|DeserializationContext|SerializationContext
     */
    private function mapContextAttributes(ContextInterface $context, JMSContext $jmsContext)
    {
        foreach ($context->getAttributes() as $key => $value) {
            $jmsContext->attributes->set($key, $value);
        }
        if (null !== $context->getVersion()) {
            $jmsContext->setVersion($context->getVersion());
        }
        if (null !== $context->getGroups()) {
            $jmsContext->setGroups($context->getGroups());
        }
        if (null !== $context->isMaxDepthEnabled()) {
            $jmsContext->enableMaxDepthChecks();
        }
        if (null !== $context->getSerializeNull()) {
            $jmsContext->setSerializeNull($context->getSerializeNull());
        }

        foreach ($context->getExclusionStrategies() as $strategy) {
            $jmsContext->addExclusionStrategy($strategy);
        }

        return $jmsContext;
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
        $context = $this->convertContext($context, self::SERIALIZATION);

        return $this->serializer->toArray($data, $context);
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
        $context = $this->convertContext($context, self::DESERIALIZATION);

        return $this->serializer->fromArray($data, $context);
    }
}
