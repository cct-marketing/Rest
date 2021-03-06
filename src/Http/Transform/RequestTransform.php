<?php

declare(strict_types=1);

namespace CCT\Component\Rest\Http\Transform;

use CCT\Component\Rest\Serializer\ContextInterface;
use CCT\Component\Rest\Transformer\Request\RequestTransformerInterface;

class RequestTransform implements RequestTransformInterface
{
    /**
     * @var RequestTransformerInterface[]|\Closure[]
     */
    protected $transformers;

    /**
     * RequestTransform constructor.
     *
     * @param array $transformers
     */
    public function __construct(array $transformers = [])
    {
        $this->transformers = $transformers;
    }

    /**
     * Tries to identify the data object sent, and convert them
     * into an array properly handled
     *
     * @param array|object $formData
     * @param ContextInterface|null $context
     *
     * @return array|object
     */
    public function transform($formData = [], ContextInterface $context = null): array
    {
        if (empty($formData)) {
            return $formData;
        }

        foreach ($this->transformers as $transformer) {
            $formData = $this->applyRequestTransformers($transformer, $formData, $context);
        }

        return $formData;
    }

    /**
     * Applied single request transformer
     *
     * @param RequestTransformerInterface|\Closure $transformer
     * @param array|object $formData
     * @param ContextInterface|null $context
     *
     * @return array|mixed
     */
    protected function applyRequestTransformers($transformer, $formData, ContextInterface $context = null)
    {
        if ($transformer instanceof RequestTransformerInterface && $transformer->supports($formData)) {
            return $transformer->transform($formData, $context);
        }

        if ($transformer instanceof \Closure) {
            return $transformer($formData);
        }

        return $formData;
    }
}
