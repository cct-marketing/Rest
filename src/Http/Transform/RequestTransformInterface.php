<?php

declare(strict_types=1);

namespace CCT\Component\Rest\Http\Transform;

use CCT\Component\Rest\Serializer\ContextInterface;

interface RequestTransformInterface
{
    /**
     * Tries to identify the data object sent, and convert them
     * into an array properly
     *
     * @param array|object $formData
     * @param ContextInterface|null $context
     *
     * @return array
     */
    public function transform(array $formData = [], ContextInterface $context = null): array;
}
