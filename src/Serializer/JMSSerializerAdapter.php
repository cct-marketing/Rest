<?php

namespace CCT\Component\Rest\Serializer;

use CCT\Component\Rest\Serializer\Context\Context;
use JMS\Serializer\Context as JMSContext;
use JMS\Serializer\ContextFactory\DeserializationContextFactoryInterface;
use JMS\Serializer\ContextFactory\SerializationContextFactoryInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;

/**
 * Adapter to plug the JMS serializer into the FOSRestBundle Serializer API.
 */
class JMSSerializerAdapter implements SerializerInterface
{
    /**
     * @var Serializer
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
        Serializer $serializer,
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
        $context = $this->convertSerializationContext($context);

        return $this->serializer->serialize($data, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function deserialize($data, $type, $format, ContextInterface $context = null)
    {
        $context = $this->convertDeserializationContext($context);

        return $this->serializer->deserialize($data, $type, $format, $context);
    }

    /**
     * @param ContextInterface|Context $context
     *
     * @return JMSContext|SerializationContext
     */
    private function convertSerializationContext(ContextInterface $context = null)
    {
        if (null === $context) {
            return null;
        }

        return $this->mapContextAttributes($context, $this->createSerializationContext());
    }

    /**
     * @param ContextInterface|Context $context
     *
     * @return JMSContext|DeserializationContext
     */
    private function convertDeserializationContext(ContextInterface $context = null)
    {
        if (null === $context) {
            return null;
        }
        $deserializationContext = $this->createDeserializationContext();

        $this->mapMaxDepth($context, $deserializationContext);

        return $this->mapContextAttributes($context, $deserializationContext);
    }

    /**
     * Create JMS serialization context
     *
     * @return SerializationContext
     */
    private function createSerializationContext()
    {
        return $this->serializationContextFactory
            ? $this->serializationContextFactory->createSerializationContext()
            : SerializationContext::create();
    }

    /**
     * Create JMS deserialization context
     *
     * @return DeserializationContext
     */
    private function createDeserializationContext()
    {
        return $this->deserializationContextFactory
            ? $this->deserializationContextFactory->createDeserializationContext()
            : DeserializationContext::create();
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

        $this->mapVersion($context, $jmsContext);

        $this->mapGroups($context, $jmsContext);

        $this->mapMaxDepthEnabled($context, $jmsContext);

        $this->mapSerializeNull($context, $jmsContext);

        $this->mapExclusionStrategies($context, $jmsContext);

        return $jmsContext;
    }

    /**
     * @param ContextInterface $context
     * @param DeserializationContext $jmsContext
     */
    private function mapMaxDepth(ContextInterface $context, DeserializationContext $jmsContext)
    {
        $maxDepth = $context->getMaxDepth();
        if (null === $maxDepth) {
            return;
        }

        for ($i = 0; $i < $maxDepth; ++$i) {
            $jmsContext->increaseDepth();
        }
    }

    /**
     * @param ContextInterface $context
     * @param JMSContext $jmsContext
     */
    private function mapVersion(ContextInterface $context, JMSContext $jmsContext)
    {
        if (null === $context->getVersion()) {
            return;
        }

        $jmsContext->setVersion($context->getVersion());
    }

    /**
     * @param ContextInterface $context
     * @param JMSContext $jmsContext
     */
    private function mapGroups(ContextInterface $context, JMSContext $jmsContext)
    {
        if (null === $context->getGroups()) {
            return;
        }

        $jmsContext->setGroups($context->getGroups());
    }

    /**
     * @param ContextInterface $context
     * @param JMSContext $jmsContext
     */
    private function mapMaxDepthEnabled(ContextInterface $context, JMSContext $jmsContext)
    {
        if (null === $context->isMaxDepthEnabled()) {
            return;
        }

        $jmsContext->enableMaxDepthChecks();
    }

    /**
     * @param ContextInterface $context
     * @param JMSContext $jmsContext
     */
    private function mapSerializeNull(ContextInterface $context, JMSContext $jmsContext)
    {
        if (null === $context->getSerializeNull()) {
            return;
        }

        $jmsContext->setSerializeNull($context->getSerializeNull());
    }

    /**
     * @param ContextInterface $context
     * @param JMSContext $jmsContext
     */
    private function mapExclusionStrategies(ContextInterface $context, JMSContext $jmsContext)
    {
        foreach ($context->getExclusionStrategies() as $strategy) {
            $jmsContext->addExclusionStrategy($strategy);
        }
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
        $context = $this->convertSerializationContext($context);

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
        $context = $this->convertDeserializationContext($context);

        return $this->serializer->fromArray($data, $context);
    }
}
