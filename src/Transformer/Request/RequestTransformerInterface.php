<?php

declare(strict_types=1);

namespace CCT\Component\Rest\Transformer\Request;

use CCT\Component\Rest\Serializer\ContextInterface;

interface RequestTransformerInterface
{
    /**
     * Transform the form data to acceptable format for request.
     *
     * @param array|object $formData
     * @param ContextInterface|null $context
     *
     * @return array
     */
    public function transform($formData = [], ContextInterface $context = null): array;

    /**
     * Checks if the formData is supported to execute the transformation.
     *
     * @param array|object $formData
     *
     * @return bool
     */
    public function supports($formData): bool;
}
