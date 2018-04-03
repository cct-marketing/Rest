<?php

namespace CCT\Component\Rest\Http;

use CCT\Component\Rest\Exception\InvalidParameterException;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class Response extends BaseResponse implements ResponseInterface
{
    /**
     * @var array
     */
    protected $data;

    /**
     * Response constructor.
     *
     * @param string $content
     * @param int $status
     * @param array $headers
     *
     * @throws \CCT\Component\Rest\Exception\InvalidParameterException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function __construct(string $content = '', int $status = 200, array $headers = array())
    {
        parent::__construct($content, $status, $headers);

        $this->data = $this->jsonToArray($content);
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function setData($data): void
    {
        $this->data = $data;
    }

    /**
     * @param string|null $content
     *
     * @return array|null
     * @throws \CCT\Component\Rest\Exception\InvalidParameterException
     * @throws \RuntimeException
     */
    protected function jsonToArray(string $content = null): ?array
    {
        if (null === $content || '' === trim($content)) {
            return null;
        }

        $this->checkContentType();

        $data = @json_decode($content, true);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('It was not possible to convert the current content to JSON.');
        }

        return $data;
    }

    /**
     * Check if content type is json
     *
     * @throws \CCT\Component\Rest\Exception\InvalidParameterException
     */
    protected function checkContentType()
    {
        if (false === strpos($this->headers->get('Content-Type'), 'json')) {
            throw new InvalidParameterException('The content returned must be in a JSON format.');
        }
    }
}
