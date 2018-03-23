<?php

declare(strict_types=1);

namespace CCT\Component\Rest\Http\Transform;

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
     *
     * @return array
     */
    public function transform($formData = [])
    {
        if (empty($formData)) {
            return $formData;
        }

        foreach ($this->transformers as $transformer) {
            $this->applyRequestTransformers($transformer, $formData);
        }

        return $formData;
    }


    /**
     * Applied single request transformer
     *
     * @param RequestTransformerInterface|\Closure $transformer
     * @param array|object $formData
     */
    protected function applyRequestTransformers($transformer, $formData)
    {
        if ($transformer instanceof RequestTransformerInterface && $transformer->supports($formData)) {
            $transformer->transform($formData);
        }

        if ($transformer instanceof \Closure) {
            $transformer($formData);
        }
    }
}
