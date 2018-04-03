<?php

declare(strict_types=1);

namespace CCT\Component\Rest\Http;

use CCT\Component\Rest\Config;
use CCT\Component\Rest\Http\Definition\QueryParams;
use CCT\Component\Rest\Http\Transform\RequestTransformInterface;
use CCT\Component\Rest\Http\Transform\ResponseTransformInterface;
use CCT\Component\Rest\Serializer\Context\Context;
use CCT\Component\Rest\Serializer\SerializerInterface;
use GuzzleHttp\Client as GuzzleClient;

abstract class AbstractSerializerRequest extends AbstractRequest implements SerializerRequestInterface
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var RequestTransformInterface
     */
    protected $requestTransform;

    /**
     * @var ResponseTransformInterface
     */
    protected $responseTransform;

    /**
     * AbstractSerializerRequest constructor.
     *
     * @param GuzzleClient $client
     * @param Config $config
     * @param SerializerInterface|null $serializer
     * @param RequestTransformInterface $requestTransform
     * @param ResponseTransformInterface $responseTransform
     */
    public function __construct(
        GuzzleClient $client,
        Config $config,
        SerializerInterface $serializer = null,
        RequestTransformInterface $requestTransform = null,
        ResponseTransformInterface $responseTransform = null
    ) {
        parent::__construct($client, $config);

        $this->serializer = $serializer;
        $this->requestTransform = $requestTransform;
        $this->responseTransform = $responseTransform;
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $formData
     * @param QueryParams|null $queryParams
     *
     * @return ResponseInterface
     * @throws \ReflectionException
     * @throws \CCT\Component\Rest\Exception\InvalidParameterException
     * @throws \RuntimeException
     * @throws \GuzzleHttp\Exception\RequestException
     * @throws \CCT\Component\Rest\Exception\ServiceUnavailableException
     */
    protected function execute(
        $method,
        string $uri,
        $formData = [],
        QueryParams $queryParams = null
    ): ResponseInterface {
        $response = parent::execute($method, $uri, $formData, $queryParams);

        if (null !== $this->responseTransform) {
            $this->responseTransform->transform(
                $response,
                $this->config->get('serialization_context')
            );
        }

        $this->config->set('serialization_context', []);

        return $response;
    }

    /**
     * @param array|object $formData
     * @param QueryParams|null $queryParams
     *
     * @return array
     */
    protected function getRequestOptions(array $formData = [], QueryParams $queryParams = null): array
    {
        if (null !== $this->requestTransform) {
            $formData = $this->requestTransform->transform(
                $formData,
                $this->config->get('serialization_context')
            );
        }

        return parent::getRequestOptions($formData, $queryParams);
    }

    /**
     * Sets the Serialization context based in the groups the request should deal with.
     *
     * @param array $groups
     *
     * @return void
     */
    protected function setSerializationContextFor(array $groups = []): void
    {
        $serializationContext = Context::create()->setGroups($groups);

        $this->config->set('serialization_context', $serializationContext);
    }
}
