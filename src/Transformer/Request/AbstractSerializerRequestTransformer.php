<?php

declare(strict_types=1);

namespace CCT\Component\Rest\Transformer\Request;

use CCT\Component\Rest\Serializer\ContextInterface;
use CCT\Component\Rest\Serializer\SerializerInterface;

abstract class AbstractSerializerRequestTransformer implements RequestTransformerInterface
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var ContextInterface|null
     */
    protected $serializationContext;

    /**
     * FormObjectTransformer constructor.
     *
     * @param SerializerInterface $serializer
     * @param ContextInterface|null $context
     */
    public function __construct(SerializerInterface $serializer, ContextInterface $context = null)
    {
        $this->serializer = $serializer;
        $this->serializationContext = $context;
    }
}
