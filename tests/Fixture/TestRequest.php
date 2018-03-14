<?php

namespace CCT\Component\Rest\Tests\Fixture;

use CCT\Component\Rest\Config;
use CCT\Component\Rest\Http\Definition\RequestHeaders;
use CCT\Component\Rest\Http\Request;
use CCT\Component\Rest\Serializer\Context\Context;

class TestRequest extends Request
{
    protected function setUp()
    {
        $this->config->set(Config::URI_PREFIX, '/test/');
    }

    public function apiCall(QueryParams $queryParams = null)
    {
        $this->config->set('serialization_context', Context::create()->setGroups(['read']));

        $headers = RequestHeaders::create(
            [
                'Accept' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
                'X-Accept-Version' => $this->config->get(Config::API_VERSION),
                'X-CCT-Auth' => $this->config->get(Config::API_KEY),
            ]
        );

        $this->setHeaders($headers);

        return parent::requestGet($this->getUri(), $queryParams);
    }
}
