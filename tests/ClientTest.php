<?php

namespace CCT\Component\Rest\Tests;

use CCT\Component\Rest\Config;
use CCT\Component\Rest\Tests\Fixture\TestClient;
use CCT\Component\Rest\Tests\Fixture\TestSerializerRequest;
use JMS\Serializer\Serializer;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class ClientTest extends TestCase
{
    protected $client;

    protected $config;

    /**
     * @var Serializer
     */
    protected $serializer;

    protected function setup(): void
    {
        $this->config = new Config([
            Config::ENDPOINT => 'http://example.com'
        ]);

        $this->client = new TestClient($this->config, true);
    }

    public function testClientCreationWithoutEndpoint()
    {
        $this->expectException(InvalidArgumentException::class);

        $config = new Config();
        new TestClient($config);
    }

    public function testTestRequestInstance()
    {
        $this->assertInstanceOf(TestSerializerRequest::class, $this->client->apiTest());
    }

}
