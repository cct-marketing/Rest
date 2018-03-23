<?php

declare(strict_types=1);

namespace CCT\Component\Rest\Http\Transform;

interface RequestTransformInterface
{
    /**
     * Tries to identify the data object sent, and convert them
     * into an array properly
     *
     * @param array|object $formData
     *
     * @return array
     */
    public function transform($formData = []);
}
