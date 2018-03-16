<?php

declare(strict_types=1);

namespace CCT\Component\Rest\Form\Normalizer;

interface FormNormalizerInterface
{
    /**
     * Normalizes the form data to acceptable format for Kong API.
     *
     * @param array|object $formData
     *
     * @return array
     */
    public function normalize($formData = []): array;
}
