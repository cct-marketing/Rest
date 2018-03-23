<?php

declare(strict_types=1);

namespace CCT\Component\Rest\Http;

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
     * @param GuzzleClient $client
     */
    public function __construct(GuzzleClient $client)
    {
        $this->client = $client;
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
     * @return PsrResponseInterface|\Symfony\Component\HttpFoundation\Response
     */
    protected function execute($method, string $uri, $formData = [], QueryParams $queryParams = null)
    {
        $options = $this->getRequestOptions($formData);

        $queryParams = $queryParams ?: new QueryParams();
        $uri = $this->addQueryParamsToUri($uri, $queryParams);

        $response = $this->sendRequest($method, $uri, $options);

        return $response;
    }

    /**
     * @param array|object $formData
     *
     * @return array
     */
    protected function getRequestOptions($formData = [])
    {
        return [
            'form_params' => $formData,
            'headers' => $this->getHeaders()->toArray()
        ];
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     *
     * @throws ServiceUnavailableException
     *
     * @return Response|object
     */
    protected function sendRequest($method, string $uri, $options = [])
    {
        try {
            $response = $this->client->request($method, $uri, $options);
        } catch (ConnectException $e) {
            throw new ServiceUnavailableException($e->getRequest(), $e->getMessage());
        } catch (RequestException $e) {
            if (null === $e->getResponse()->getBody()) {
                throw $e;
            }
            $response = $e->getResponse();
        }

        return $response;
    }

    /**
     * Adds a query string params to the URI.
     *
     * @param string $uri
     * @param QueryParams $queryParams
     *
     * @return string
     */
    protected function addQueryParamsToUri(string $uri, QueryParams $queryParams)
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
}
