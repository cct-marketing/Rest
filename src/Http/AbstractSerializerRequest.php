<?php

declare(strict_types=1);

namespace CCT\Component\Rest\Http;

use Assert\Assert;
use CCT\Component\Rest\Config;
use CCT\Component\Rest\Exception\InvalidParameterException;
use CCT\Component\Rest\Http\Definition\QueryParams;
use CCT\Component\Rest\Http\Transform\RequestTransformInterface;
use CCT\Component\Rest\Http\Transform\ResponseTransformInterface;
use CCT\Component\Rest\Serializer\Context\Context;
use CCT\Component\Rest\Serializer\SerializerInterface;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

abstract class AbstractSerializerRequest extends AbstractRequest implements SerializerRequestInterface
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var Config
     */
    protected $config;

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
     * @param null $serializer
     * @param RequestTransformInterface $requestTransform
     * @param ResponseTransformInterface $responseTransform
     */
    public function __construct(
        GuzzleClient $client,
        Config $config,
        $serializer = null,
        RequestTransformInterface $requestTransform = null,
        ResponseTransformInterface $responseTransform = null
    ) {
        parent::__construct($client);

        $this->serializer = $serializer;
        $this->config = $config;

        $this->setUp();
        $this->validateConfig();

        $this->requestTransform = $requestTransform;

        $this->responseTransform = $responseTransform;
    }

    /**
     * @return string|null
     */
    public function getUri()
    {
        return $this->config->get(Config::URI_PREFIX);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array|object $formData
     * @param QueryParams|null $queryParams
     *
     * @return ResponseInterface
     */
    protected function execute($method, string $uri, $formData = [], QueryParams $queryParams = null)
    {
        $response = $this->createResponseRefFromResponse(
            parent::execute($method, $uri, $formData, $queryParams)
        );

        if (null !== $this->responseTransform) {
            $this->responseTransform->transform($response);
        }
        $this->config->set('serialization_context', []);

        return $response;
    }

    /**
     * @param array|object $formData
     *
     * @return array
     */
    protected function getRequestOptions($formData = [])
    {
        if (null !== $this->requestTransform) {
            $formData = $this->requestTransform->transform($formData);
        }

        return [
            'form_params' => $formData,
            'headers' => $this->getHeaders()->toArray()
        ];
    }

    /**
     * Create Response reflection from a response
     *
     * @param PsrResponseInterface $response
     *
     * @return ResponseInterface|object
     */
    protected function createResponseRefFromResponse(PsrResponseInterface $response)
    {
        $responseRef = $this->createResponseReflectionInstance();

        return $responseRef->newInstance(
            $response->getBody()->getContents(),
            $response->getStatusCode(),
            $response->getHeaders()
        );
    }

    /**
     * @param string $uri
     *
     * @return string
     */
    protected function formatUri(string $uri): string
    {
        $baseUri = $this->client->getConfig('base_uri');

        // todo: review
        return ($baseUri instanceof Uri && $baseUri->getPath())
            ? rtrim($baseUri->getPath(), '/') . '/' . ltrim($uri, '/')
            : $uri;
    }

    /**
     * Appends new parameters to the URI.
     *
     * @param string $complement
     * @param string|null $uri
     *
     * @return string
     */
    protected function appendToUri(string $complement, ?string $uri = null)
    {
        $uri = $uri ?: $this->config->get(Config::URI_PREFIX);

        return sprintf(
            '%s/%s',
            rtrim($uri, '/'),
            ltrim($complement, '/')
        );
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

    /**
     * Creates a Reflection Response class.
     *
     * @return \ReflectionClass
     */
    private function createResponseReflectionInstance(): \ReflectionClass
    {
        $responseClass = $this->config->get(Config::RESPONSE_CLASS, Response::class);
        $responseRef = new \ReflectionClass($responseClass);

        if (!$responseRef->implementsInterface(ResponseInterface::class)) {
            throw new InvalidParameterException(sprintf(
                'The response class must be an implementation of %s',
                ResponseInterface::class
            ));
        }

        return $responseRef;
    }

    /**
     * Validates the required parameters from Config file.
     *
     * @return void
     */
    private function validateConfig()
    {
        Assert::lazy()
            ->that($this->config->toArray(), Config::URI_PREFIX)->keyExists(Config::URI_PREFIX)
            ->verifyNow();
    }

    /**
     * Initialization of the request.
     *
     * @return void
     */
    abstract protected function setUp();
}
