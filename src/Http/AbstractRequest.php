<?php

declare(strict_types=1);

namespace CCT\Component\Rest\Http;

use Assert\Assert;
use CCT\Component\Rest\Config;
use CCT\Component\Rest\Exception\InvalidParameterException;
use CCT\Component\Rest\Exception\ServiceUnavailableException;
use CCT\Component\Rest\Form\Normalizer\DefaultFormNormalizer;
use CCT\Component\Rest\Form\Normalizer\FormNormalizerInterface;
use CCT\Component\Rest\Http\Definition\QueryParams;
use CCT\Component\Rest\Http\Definition\RequestHeaders;
use CCT\Component\Rest\Serializer\Context\Context;
use CCT\Component\Rest\Serializer\SerializerInterface;
use CCT\Component\Rest\Transformer\TransformerInterface;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Uri;

abstract class AbstractRequest implements RequestInterface
{
    /**
     * @var GuzzleClient
     */
    protected $client;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var Config
     */
    protected $config;

    /**
     * Request headers
     *
     * @var RequestHeaders
     */
    protected $headers;

    /**
     * @param GuzzleClient $client
     * @param SerializerInterface $serializer
     * @param Config $config
     */
    public function __construct(GuzzleClient $client, Config $config, $serializer = null)
    {
        $this->client = $client;
        $this->serializer = $serializer;
        $this->config = $config;

        $this->setUp();
        $this->validateConfig();
    }

    /**
     * @return string|null
     */
    public function getUri()
    {
        return $this->config->get(Config::URI_PREFIX);
    }

    /**
     * @param string $uri
     * @param QueryParams|null $queryParams
     *
     * @return ResponseInterface|\Symfony\Component\HttpFoundation\Response
     */
    protected function requestGet($uri, QueryParams $queryParams = null)
    {
        return $this->execute(self::METHOD_GET, $uri, [], $queryParams);
    }

    /**
     * @param string $uri
     * @param QueryParams|null $queryParams
     *
     * @return ResponseInterface|\Symfony\Component\HttpFoundation\Response
     */
    protected function requestDelete($uri, QueryParams $queryParams = null)
    {
        return $this->execute(self::METHOD_DELETE, $uri, [], $queryParams);
    }

    /**
     * @param string $uri
     * @param array|object $formData
     * @param QueryParams|null $queryParams
     *
     * @return ResponseInterface|\Symfony\Component\HttpFoundation\Response
     */
    protected function requestPost($uri, $formData, QueryParams $queryParams = null)
    {
        return $this->execute(self::METHOD_POST, $uri, $formData, $queryParams);
    }

    /**
     * @param string $uri
     * @param array|object $formData
     * @param QueryParams|null $queryParams
     *
     * @return ResponseInterface|\Symfony\Component\HttpFoundation\Response
     */
    protected function requestPatch($uri, $formData, QueryParams $queryParams = null)
    {
        return $this->execute(self::METHOD_PATCH, $uri, $formData, $queryParams);
    }

    /**
     * @param string $uri
     * @param array|object $formData
     * @param QueryParams|null $queryParams
     *
     * @return ResponseInterface|\Symfony\Component\HttpFoundation\Response
     */
    protected function requestPut($uri, $formData, QueryParams $queryParams = null)
    {
        return $this->execute(self::METHOD_PUT, $uri, $formData, $queryParams);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array|object $formData
     * @param QueryParams|null $queryParams
     *
     * @return ResponseInterface|\Symfony\Component\HttpFoundation\Response
     */
    protected function execute($method, string $uri, $formData = [], QueryParams $queryParams = null)
    {
        $queryParams = $queryParams ?: new QueryParams();
        $options = $this->normalizeFormData($formData);
        $uri = $this->normalizeUri($uri, $queryParams);

        $options = array_merge($options, ['headers' => $this->getHeaders()->toArray()]);

        $response = $this->sendRequest($method, $uri, $options);
        $this->applyResponseTransformers($response);

        $this->config->set('serialization_context', []);

        return $response;
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $formData
     *
     * @throws ServiceUnavailableException
     *
     * @return Response|object
     */
    private function sendRequest($method, string $uri, $formData = [])
    {
        $responseRef = $this->createResponseReflectionInstance();
        $baseUri = $this->client->getConfig('base_uri');

        // todo: review
        $uri = ($baseUri instanceof Uri && $baseUri->getPath())
            ? rtrim($baseUri->getPath(), '/') . '/' . ltrim($uri, '/')
            : $uri;

        try {
            $psrResponse = $this->client->request($method, $uri, $formData);

            $response = $responseRef->newInstance(
                $psrResponse->getBody()->getContents(),
                $psrResponse->getStatusCode(),
                $psrResponse->getHeaders()
            );
        } catch (ConnectException $e) {
            throw new ServiceUnavailableException($e->getRequest(), $e->getMessage());
        } catch (RequestException $e) {
            if (null === $e->getResponse()->getBody()) {
                throw $e;
            }

            $response = $responseRef->newInstance(
                $e->getResponse()->getBody()->getContents(),
                $e->getResponse()->getStatusCode(),
                $e->getResponse()->getHeaders()
            );
        }

        return $response;
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
     * It is possible to handle the Response data defining the Config key response_transformers
     * with an instance of Closure or an instance of TransformerInterface.
     *
     * @param ResponseInterface $data
     *
     * @return void
     */
    protected function applyResponseTransformers(ResponseInterface $data)
    {
        foreach ($this->config->get(Config::RESPONSE_TRANSFORMERS, []) as $transformer) {
            if ($transformer instanceof TransformerInterface && $transformer->supports($data)) {
                $transformer->transform($data);
            }

            if ($transformer instanceof \Closure) {
                $transformer($data);
            }
        }
    }

    /**
     * Tries to identify the data object sent, and convert them
     * into an array properly handled by the JMSSerializer
     * and for the acceptance of Kong API.
     *
     * @param array|object $formData
     *
     * @return array
     */
    private function normalizeFormData($formData = [])
    {
        if (empty($formData)) {
            return $formData;
        }
        $defaultFormNormalizer = new DefaultFormNormalizer(
            $this->serializer,
            $this->config->get('serialization_context')
        );

        $formNormalizer = $this->config->get(Config::FORM_NORMALIZER, $defaultFormNormalizer);
        if (!$formNormalizer instanceof FormNormalizerInterface) {
            return [];
        }

        $formParams = $formNormalizer->normalize($formData);

        if (!empty($formParams) && !isset($formParams['form_params'])) {
            $formParams = ['form_params' => $formParams];
        }

        return $formParams;
    }

    /**
     * Adds a query string params to the URI.
     *
     * @param string $uri
     * @param QueryParams $queryParams
     *
     * @return string
     */
    private function normalizeUri(string $uri, QueryParams $queryParams)
    {
        if (false !== strpos($uri, '?')) {
            throw new InvalidParameterException(sprintf(
                'It was not possible to normalize the URI as the current URI %s already 
                has the interrogation char in its string.' .
                $uri
            ));
        }

        return $uri . $queryParams->toString();
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

    protected function disableFormNormalizer()
    {
        $this->config->set(Config::FORM_NORMALIZER, null);
    }

    /**
     * Validates if the object has a valid id, otherwise throws an exception.
     *
     * @param object $object
     *
     * @return void
     */
    protected function validateObjectId($object)
    {
        if (!method_exists($object, 'getId') || null === $object->getId()) {
            throw new InvalidParameterException(sprintf(
                'The object "%s" must have an ID to continue the operation. "%s" given.',
                get_class($object),
                gettype($object->getId())
            ));
        }
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
     * Set headers for request
     *
     * @param RequestHeaders $headers
     */
    protected function setHeaders(RequestHeaders $headers)
    {
        $this->headers = $headers;
    }

    /**
     * Get headers for request
     *
     * @return RequestHeaders
     */
    protected function getHeaders(): RequestHeaders
    {
        return $this->headers;
    }

    /**
     * Initialization of the request.
     *
     * @return void
     */
    abstract protected function setUp();
}
