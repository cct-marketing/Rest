<?php

declare(strict_types=1);

namespace CCT\Component\Rest\Http;

use CCT\Component\Rest\Config;
use CCT\Component\Rest\Exception\InvalidParameterException;
use CCT\Component\Rest\Exception\ServiceUnavailableException;
use CCT\Component\Rest\Http\Definition\QueryParams;
use CCT\Component\Rest\Http\Definition\RequestHeaders;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

abstract class AbstractRequest implements RequestInterface
{
    /**
     * @var GuzzleClient
     */
    protected $client;

    /**
     * Request headers
     *
     * @var RequestHeaders
     */
    protected $headers;

    /**
     * The name of the response class used to
     *
     * @var Config
     */
    protected $config;

    /**
     * AbstractRequest constructor.
     *
     * @param GuzzleClient $client
     * @param Config $config
     */
    public function __construct(GuzzleClient $client, Config $config)
    {
        $this->client = $client;
        $this->config = $config;

        $this->setUp();
    }

    /**
     * @param string $uri
     * @param QueryParams|null $queryParams
     *
     * @return ResponseInterface
     * @throws \ReflectionException
     * @throws \CCT\Component\Rest\Exception\InvalidParameterException
     * @throws \RuntimeException
     * @throws \GuzzleHttp\Exception\RequestException
     * @throws \CCT\Component\Rest\Exception\ServiceUnavailableException
     */
    protected function requestGet($uri, QueryParams $queryParams = null): ResponseInterface
    {
        return $this->execute(self::METHOD_GET, $uri, [], $queryParams);
    }

    /**
     * @param string $uri
     * @param QueryParams|null $queryParams
     *
     * @return ResponseInterface
     * @throws \ReflectionException
     * @throws \CCT\Component\Rest\Exception\InvalidParameterException
     * @throws \RuntimeException
     * @throws \GuzzleHttp\Exception\RequestException
     * @throws \CCT\Component\Rest\Exception\ServiceUnavailableException
     */
    protected function requestDelete($uri, QueryParams $queryParams = null): ResponseInterface
    {
        return $this->execute(self::METHOD_DELETE, $uri, [], $queryParams);
    }

    /**
     * @param string $uri
     * @param array|object $formData
     * @param QueryParams|null $queryParams
     *
     * @return ResponseInterface
     * @throws \ReflectionException
     * @throws \CCT\Component\Rest\Exception\InvalidParameterException
     * @throws \RuntimeException
     * @throws \GuzzleHttp\Exception\RequestException
     * @throws \CCT\Component\Rest\Exception\ServiceUnavailableException
     */
    protected function requestPost($uri, $formData, QueryParams $queryParams = null): ResponseInterface
    {
        return $this->execute(self::METHOD_POST, $uri, $formData, $queryParams);
    }

    /**
     * @param string $uri
     * @param array|object $formData
     * @param QueryParams|null $queryParams
     *
     * @return ResponseInterface
     * @throws \ReflectionException
     * @throws \CCT\Component\Rest\Exception\InvalidParameterException
     * @throws \RuntimeException
     * @throws \GuzzleHttp\Exception\RequestException
     * @throws \CCT\Component\Rest\Exception\ServiceUnavailableException
     */
    protected function requestPatch($uri, $formData, QueryParams $queryParams = null): ResponseInterface
    {
        return $this->execute(self::METHOD_PATCH, $uri, $formData, $queryParams);
    }

    /**
     * @param string $uri
     * @param array|object $formData
     * @param QueryParams|null $queryParams
     *
     * @return ResponseInterface
     * @throws \ReflectionException
     * @throws \CCT\Component\Rest\Exception\InvalidParameterException
     * @throws \RuntimeException
     * @throws \GuzzleHttp\Exception\RequestException
     * @throws \CCT\Component\Rest\Exception\ServiceUnavailableException
     */
    protected function requestPut($uri, $formData, QueryParams $queryParams = null): ResponseInterface
    {
        return $this->execute(self::METHOD_PUT, $uri, $formData, $queryParams);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array|object $formData
     * @param QueryParams|null $queryParams
     *
     * @return ResponseInterface
     * @throws \ReflectionException
     * @throws \CCT\Component\Rest\Exception\InvalidParameterException
     * @throws \GuzzleHttp\Exception\RequestException
     * @throws \RuntimeException
     * @throws \CCT\Component\Rest\Exception\ServiceUnavailableException
     */
    protected function execute(
        $method,
        string $uri,
        $formData = [],
        QueryParams $queryParams = null
    ): ResponseInterface {
        $options = $this->getRequestOptions($formData, $queryParams);

        $response = $this->sendRequest($method, $uri, $options);

        return $this->createResponseRefFromResponse($response);
    }

    /**
     * @param array|object $formData
     * @param QueryParams|null $queryParams
     *
     * @return array
     */
    protected function getRequestOptions($formData = [], QueryParams $queryParams = null): array
    {
        return [
            'form_params' => $formData,
            'headers' => $this->getHeaders()->all(),
            'query' => $queryParams !== null ? $queryParams->all() : []
        ];
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     *
     * @throws \GuzzleHttp\Exception\RequestException
     * @throws ServiceUnavailableException
     *
     * @return PsrResponseInterface|object
     */
    protected function sendRequest($method, string $uri, array $options = [])
    {
        try {
            $response = $this->client->request($method, $uri, $options);
        } catch (ConnectException $e) {
            throw new ServiceUnavailableException($e->getRequest(), $e->getMessage());
        } catch (RequestException $e) {
            if (true === $e->hasResponse() && null !== $e->getResponse()->getBody()) {
                $response = $e->getResponse();
            }
            throw $e;
        }

        return $response;
    }

    /**
     * Set headers for request
     *
     * @param RequestHeaders $headers
     */
    protected function setHeaders(RequestHeaders $headers): void
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
     * Create Response reflection from a response
     *
     * @param PsrResponseInterface $response
     *
     * @return ResponseInterface|object
     * @throws \ReflectionException
     * @throws \CCT\Component\Rest\Exception\InvalidParameterException
     * @throws \RuntimeException
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
     * Creates a Reflection Response class.
     *
     * @return \ReflectionClass
     * @throws \CCT\Component\Rest\Exception\InvalidParameterException
     * @throws \ReflectionException
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
     * @return string|null
     */
    public function getUri(): ?string
    {
        return $this->config->get(Config::URI_PREFIX, '/');
    }

    /**
     * Initialization of the request.
     *
     * @return void
     */
    abstract protected function setUp(): void;
}
