<?php

declare(strict_types=1);

namespace CCT\Component\Rest\Form\Normalizer;

use CCT\Component\Rest\Serializer\ContextInterface;
use CCT\Component\Rest\Serializer\SerializerInterface;
use JMS\Serializer\Serializer;

class DefaultFormNormalizer implements FormNormalizerInterface
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var ContextInterface|null
     */
    protected $serializationContext;

    public function __construct(SerializerInterface $serializer, ContextInterface $context = null)
    {
        $this->serializer = $serializer;
        $this->serializationContext = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($formData = []): array
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

        $formParams = $this->normalizeParams($formData);

        return $formParams;
    }

    protected function normalizeParams(array $formData): array
    {
        $formParams = [];
        foreach ($formData as $key => $value) {
            if (empty($value)) {
                continue;
            }

            $formParams[$key] = (is_array($value))
                ? join(',', $value)
                : $value
            ;
        }

        return $formParams;
    }
}
