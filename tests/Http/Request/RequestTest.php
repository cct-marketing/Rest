<?php

namespace CCT\Component\Rest\Tests\Http\Request;

use CCT\Component\Rest\Config;
use CCT\Component\Rest\Http\AbstractSerializerRequest;
use CCT\Component\Rest\Http\Response;
use CCT\Component\Rest\Http\Transform\RequestTransform;
use CCT\Component\Rest\Http\Transform\ResponseTransform;
use CCT\Component\Rest\Serializer\Context\Context;
use CCT\Component\Rest\Tests\Fixture\TestModel;
use CCT\Component\Rest\Tests\Fixture\TestRequest;
use CCT\Component\Rest\Transformer\Request\FormObjectTransformer;
use CCT\Component\Rest\Transformer\Response\CollectionObjectTransformer;
use CCT\Component\Rest\Transformer\Response\ObjectTransformer;

class RequestTest extends AbstractTestRequest
{
    public function testRequestWithValidResponseNoSerializer()
    {
        $client = $this->createClientMocked(Response::HTTP_OK, 'simple_api_request.json');
        $request = $this->createTestRequest($client);
        $response = $request->apiCall();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue(is_array($response->getData()));
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testRequestWithValidResponseJMSSerializer()
    {
        $this->createJMSSerializer();
        $client = $this->createClientMocked(Response::HTTP_OK, 'simple_api_request.json');
        $request = $this->createTestRequest($client);
        $response = $request->apiCall();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertInstanceOf(TestModel::class, $response->getData());
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testRequestWithValidResponseSymfonySerializer()
    {
        $this->createSymfonySerializer();
        $client = $this->createClientMocked(Response::HTTP_OK, 'simple_api_request.json');
        $request = $this->createTestRequest($client);
        $response = $request->apiCall();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertInstanceOf(TestModel::class, $response->getData());
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    protected function createTestRequest($client): AbstractSerializerRequest
    {
        $modelClass = TestModel::class;

        $config = new Config();
        if (null === $this->getSerializer()) {
            return $this->createSerializerRequest(
                $client,
                TestRequest::class,
                $config,
                null,
                null
            );
        }

        $config->set(
            Config::REQUEST_TRANSFORMERS,
            [
                new FormObjectTransformer($this->getSerializer(), new Context()),
            ]
        );

        $config->set(
            Config::RESPONSE_TRANSFORMERS,
            [
                new ObjectTransformer($this->getSerializer(), $modelClass, new Context()),
                new CollectionObjectTransformer($this->getSerializer(), $modelClass, new Context())
            ]
        );

        $requestTransform = new RequestTransform(
            $config->get(Config::REQUEST_TRANSFORMERS, [])
        );

        $responseTransform = new ResponseTransform(
            $config->get(Config::RESPONSE_TRANSFORMERS, [])
        );

        return $this->createSerializerRequest(
            $client,
            TestRequest::class,
            $config,
            $requestTransform,
            $responseTransform
        );
    }
}
