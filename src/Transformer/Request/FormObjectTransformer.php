<?php

declare(strict_types=1);

namespace CCT\Component\Rest\Transformer\Request;

use CCT\Component\Rest\Serializer\ContextInterface;

class FormObjectTransformer extends AbstractSerializerRequestTransformer
{
    /**
     * {@inheritdoc}
     */
    public function transform($formData = [], ContextInterface $context = null): array
    {
        if (empty($formData)) {
            return [];
        }

        if (is_object($formData)) {
            return $this->normalizeObject($formData, $context);
        }

        array_walk_recursive($formData, [&$this, 'serializeObjects'], $context);

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

    /**
     * @param $item
     * @param $key
     * @param ContextInterface|null $context
     */
    protected function serializeObjects(&$item, $key, ContextInterface $context = null)
    {
        if (is_object($item)) {
            $item = $this->normalizeObject($item, $context);
        }
    }

    /**
     * @param $object
     * @param ContextInterface|null $context
     *
     * @return array
     */
    protected function normalizeObject($object, ContextInterface $context = null)
    {
        return $this->serializer->toArray(
            $object,
            $context ?? $this->context
        );
    }
}
