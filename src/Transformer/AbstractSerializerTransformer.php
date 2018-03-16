<?php

declare(strict_types=1);

namespace CCT\Component\Rest\Transformer;

use CCT\Component\Rest\Serializer\ContextInterface;
use CCT\Component\Rest\Serializer\SerializerInterface;

abstract class AbstractSerializerTransformer implements TransformerInterface
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var ContextInterface|null
     */
    protected $context;

    public function __construct(SerializerInterface $serializer, string $class, ContextInterface $context = null)
    {
        $this->serializer = $serializer;
        $this->class = $class;
        $this->context = $context;
    }
}
