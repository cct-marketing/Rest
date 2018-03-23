<?php

declare(strict_types=1);

namespace CCT\Component\Rest\Http\Transform;

use CCT\Component\Rest\Http\ResponseInterface;
use CCT\Component\Rest\Transformer\Response\ResponseTransformerInterface;

class ResponseTransform implements ResponseTransformInterface
{
    /**
     * @var array
     */
    protected $transformers;

    /**
     * ResponseTransformer constructor.
     *
     * @param array $transformers
     */
    public function __construct(array $transformers = [])
    {
        $this->transformers = $transformers;
    }

    /**
     * It is possible to handle the Response data defining the Config key response_transformers
     * with an instance of Closure or an instance of TransformerInterface.
     *
     * @param ResponseInterface $data
     *
     * @return void
     */
    public function transform(ResponseInterface $data)
    {
        foreach ($this->transformers as $transformer) {
            $this->applyResponseTransformer($transformer, $data);
        }
    }

    /**
     * Applied single response transformer
     *
     * @param ResponseTransformerInterface|\Closure $transformer
     * @param ResponseInterface $data
     */
    protected function applyResponseTransformer($transformer, ResponseInterface $data)
    {
        if ($transformer instanceof ResponseTransformerInterface && $transformer->supports($data)) {
            $transformer->transform($data);
            return;
        }

        if ($transformer instanceof \Closure) {
            $transformer($data);
        }
    }
}
