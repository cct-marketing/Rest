<?php

declare(strict_types=1);

namespace CCT\Component\Rest\Transformer;

use CCT\Component\Rest\Serializer\ContextInterface;
use CCT\Component\Rest\Serializer\SerializerInterface;

abstract class SerializerTransformer implements TransformerInterface
{
    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var SerializationContext|null
     */
    protected $context;

    public function __construct(SerializerInterface $serializer, string $class, ContextInterface $context = null)
    {
        $this->serializer = $serializer;
        $this->class = $class;
        $this->context = $context;
    }
}
