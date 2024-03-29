<?php

declare(strict_types=1);

namespace CCT\Component\Rest\Tests\Http\Request;

use CCT\Component\Rest\Config;
use CCT\Component\Rest\Http\AbstractRequest;
use CCT\Component\Rest\Http\AbstractSerializerRequest;
use CCT\Component\Rest\Http\Transform\RequestTransformInterface;
use CCT\Component\Rest\Http\Transform\ResponseTransformInterface;
use CCT\Component\Rest\Serializer\JMSSerializerBuilder;
use CCT\Component\Rest\Serializer\SerializerInterface;
use CCT\Component\Rest\Serializer\SymfonySerializerBuilder;
use CCT\Component\Rest\Tests\Helper\ProtectedMethodSetter;
use GuzzleHttp\Client;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\Serializer;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Response;

abstract class AbstractTestRequest extends TestCase
{

    /**
     * @var string
     */
    protected $metadataPath;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * Path to directory containing test response
     *
     * @var string
     */
    protected $responseDirectory;

    /**
     * Namespace to models
     *
     * @var string
     */
    protected $namespacePrefix;

    /**
     * {@inheritdoc}
     */
    protected function setup(): void
    {
        $this->metadataPath = realpath(__DIR__ . '/../../Fixture/Resources/metadata');
        $this->responseDirectory = realpath(__DIR__ . '/../../Fixture/Resources/Response/');
        $this->namespacePrefix = 'CCT\\Component\\Rest\\Tests\\Fixture';

        parent::setUp();
    }

    /**
     * @param int $statusCode
     * @param string|null $contentFile
     * @param array $headers
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|Client
     */
    protected function createClientMocked(
        int $statusCode,
        string $contentFile = null,
        array $headers = ['Content-type' => 'application/json']
    ) {
        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        if (!file_exists($this->responseDirectory . '/' . $contentFile)) {
            throw new \InvalidArgumentException('The file was not found.');
        }

        $body = file_get_contents($this->responseDirectory . '/' . $contentFile);
        $client->expects($this->any())
            ->method('request')
            ->willReturn(new Response(
                $statusCode,
                $headers,
                $body
            ));

        return $client;
    }

    /**
     * @return SerializerInterface
     */
    public function createJMSSerializer(): SerializerInterface
    {
        $config = new Config();
        $config->set(Config::METADATA_DIRS, [
            [
                'dir' => $this->metadataPath,
                'namespacePrefix' => $this->namespacePrefix,
            ]
        ]);

        $this->serializer = JMSSerializerBuilder::createByConfig($config)
            ->configureDefaults()
            ->build();

        return $this->serializer;
    }

    /**
     * @return SerializerInterface
     */
    public function createSymfonySerializer(): SerializerInterface
    {
        $config = new Config();
        $config->set(Config::METADATA_DIRS, [
            [
                'dir' => $this->metadataPath,
            ]
        ]);

        $this->serializer = SymfonySerializerBuilder::createByConfig($config)
            ->configureDefaults()
            ->build();

        return $this->serializer;
    }

    /**
     * @param Client $client
     * @param string $class
     * @param Config $config
     *
     * @return AbstractRequest
     */
    protected function createRequest($client, $class, Config $config): AbstractRequest
    {
        $request = new $class($client, $config, $this->getSerializer());

        return $request;
    }

    /**
     * @param $client
     * @param $class
     * @param Config $config
     * @param RequestTransformInterface $requestTransform
     * @param ResponseTransformInterface $responseTransform
     *
     * @return AbstractSerializerRequest
     */
    protected function createSerializerRequest(
        $client,
        $class,
        Config $config,
        RequestTransformInterface $requestTransform = null,
        ResponseTransformInterface $responseTransform = null
    ): AbstractSerializerRequest {

        $request = new $class($client, $config, $this->getSerializer(), $requestTransform, $responseTransform);

        return $request;
    }

    /**
     * @return SerializerInterface
     */
    protected function getSerializer(): ?SerializerInterface
    {
        return $this->serializer;
    }
}
