<?php

namespace CCT\Component\Rest\Tests\Fixture;

use CCT\Component\Rest\Config;
use CCT\Component\Rest\Http\AbstractRequest;
use CCT\Component\Rest\Http\Definition\RequestHeaders;
use CCT\Component\Rest\Serializer\Context\Context;

class TestRequest extends AbstractRequest
{
    protected function setup(): void
    {
        $this->config->set(Config::URI_PREFIX, '/test/');
    }

    public function apiCall(QueryParams $queryParams = null)
    {
        $headers = RequestHeaders::create(
            [
                'Accept' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
                'X-Accept-Version' => $this->config->get(Config::API_VERSION),
                'X-CCT-Auth' => $this->config->get(Config::API_KEY),
            ]
        );

        $this->setHeaders($headers);

        return $this->requestGet($this->getUri(), $queryParams);
    }
}
