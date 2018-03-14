<?php

declare(strict_types=1);

namespace CCT\Component\Rest\Serializer;

interface SerializerBuilderInterface
{
    /**
     * Builds the JMS Serializer object.
     *
     * @return SerializerInterface
     */
    public function build(): SerializerInterface;
}
