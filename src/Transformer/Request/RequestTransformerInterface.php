<?php

declare(strict_types=1);

namespace CCT\Component\Rest\Transformer\Request;

interface RequestTransformerInterface
{
    /**
     * Transform the form data to acceptable format for request.
     *
     * @param array|object $formData
     *
     * @return array
     */
    public function transform($formData = []): array;

    /**
     * Checks if the formData is supported to execute the transformation.
     *
     * @param array|object $formData
     *
     * @return bool
     */
    public function supports($formData): bool;
}
