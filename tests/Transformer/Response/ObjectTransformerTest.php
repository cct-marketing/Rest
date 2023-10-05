<?php

declare(strict_types=1);

namespace CCT\Component\Rest\Tests\Transformer\Response;

use CCT\Component\Rest\Config;
use CCT\Component\Rest\Exception\InvalidParameterException;
use CCT\Component\Rest\Http\Response;
use CCT\Component\Rest\Serializer\JMSSerializerBuilder;
use CCT\Component\Rest\Serializer\SerializerInterface;
use CCT\Component\Rest\Tests\Fixture\TestModel;
use CCT\Component\Rest\Transformer\Response\ObjectTransformer;
use PHPUnit\Framework\TestCase;

class ObjectTransformerTest extends TestCase
{
    /**
     * @var string
     */
    protected $metadataPath;

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

    public function setup(): void
    {
        $this->metadataPath = realpath(__DIR__ . '/../../Fixture/Resources/metadata');
        $this->responseDirectory = realpath(__DIR__ . '/../../Fixture/Resources/Response/');
        $this->namespacePrefix = 'CCT\\Component\\Rest\\Tests\\Fixture';
    }

    public function testTransformResponseForObject()
    {
        $response = new Response(
            '{
                "heading": "heading 1",
                "body": "body 1"
            }',
            200,
            [
                'Content-Type' => 'application/json'
            ]
        );

        $objectTransformer = new ObjectTransformer($this->createJMSSerializer(), TestModel::class, null);

        $objectTransformer->transform($response);

        $this->assertInstanceOf(TestModel::class, $response->getData());
        $this->assertEquals('heading 1', $response->getData()->getHeading());
        $this->assertEquals('body 1', $response->getData()->getBody());
    }

    public function testTransformResponseForArrayWithObjectReturnsEmptyObject()
    {
        $response = new Response(
            '[{
                "heading": "heading 1",
                "body": "body 1"
            }]',
            200,
            [
                'Content-Type' => 'application/json'
            ]
        );

        $objectTransformer = new ObjectTransformer($this->createJMSSerializer(), TestModel::class, null);

        $objectTransformer->transform($response);

        $this->assertInstanceOf(TestModel::class, $response->getData());
        $this->assertNull($response->getData()->getHeading());
        $this->assertNull($response->getData()->getBody());
    }

    public function testSupportReturnsTrueForObject()
    {
        $response = new Response(
            '{
                "heading": "heading 1",
                "body": "body 1"
            }',
            200,
            [
                'Content-Type' => 'application/json'
            ]
        );

        $objectTransformer = new ObjectTransformer($this->createJMSSerializer(), TestModel::class, null);

        $this->assertTrue(
            $objectTransformer->supports($response)
        );
    }

    public function testSupportReturnsFalseForEmptyResponseContent()
    {
        $response = new Response(
            '',
            200,
            [
                'Content-Type' => 'application/json'
            ]
        );

        $objectTransformer = new ObjectTransformer($this->createJMSSerializer(), TestModel::class, null);

        $this->assertFalse(
            $objectTransformer->supports($response)
        );
    }

    public function testSupportReturnsFalseFor404Response()
    {
        $response = new Response(
            '{ "message" : "Not Found" }',
            404,
            [
                'Content-Type' => 'application/json'
            ]
        );

        $objectTransformer = new ObjectTransformer($this->createJMSSerializer(), TestModel::class, null);

        $this->assertFalse(
            $objectTransformer->supports($response)
        );
    }

    public function testSupportThrowsExceptionIfResponseNotJsonContent()
    {
        $this->expectException(InvalidParameterException::class);
        $response = new Response(
            '{ "opps" : "Content type does not match json" }',
            200,
            [
                'Content-Type' => 'application/xml'
            ]
        );
        $objectTransformer = new ObjectTransformer($this->createJMSSerializer(), TestModel::class, null);

        $this->assertFalse(
            $objectTransformer->supports($response)
        );
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

        return JMSSerializerBuilder::createByConfig($config)
            ->configureDefaults()
            ->build();
    }
}
