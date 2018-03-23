<?php

declare(strict_types=1);

namespace CCT\Component\Rest\Transformer\Request;

class FormObjectTransformer extends AbstractSerializerRequestTransformer
{

    /**
     * {@inheritdoc}
     */
    public function transform($formData = []): array
    {
        if (empty($formData)) {
            return [];
        }

        if (is_object($formData)) {
            $formData = $this->serializer->toArray(
                $formData,
                $this->serializationContext
            );
        }

        return $formData;
    }

    /**
     * Checks if the formData is supported to execute the transformation.
     *
     * @param array|object $formData
     *
     * @return bool
     */
    public function supports($formData): bool
    {
        return is_object($formData) || is_array($formData);
    }
}
