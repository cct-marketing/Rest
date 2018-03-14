<?php

namespace CCT\Component\Rest\Tests;

use CCT\Component\Rest\Config;
use CCT\Component\Rest\Tests\Fixture\TestClient;
use CCT\Component\Rest\Tests\Fixture\TestRequest;
use JMS\Serializer\Serializer;
use PHPUnit\Framework\TestCase;
use Assert\InvalidArgumentException;

class ClientTest extends TestCase
{
    protected $client;

    protected $config;

    /**
     * @var Serializer
     */
    protected $serializer;

    protected function setUp()
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
        $this->assertInstanceOf(TestRequest::class, $this->client->apiTest());
    }

}
