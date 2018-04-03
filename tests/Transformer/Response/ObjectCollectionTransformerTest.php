<?php

declare(strict_types=1);

namespace CCT\Component\Rest\Tests\Transformer\Response;

use CCT\Component\Rest\Config;
use CCT\Component\Rest\Exception\InvalidParameterException;
use CCT\Component\Rest\Http\Response;
use CCT\Component\Rest\Serializer\JMSSerializerBuilder;
use CCT\Component\Rest\Serializer\SerializerInterface;
use CCT\Component\Rest\Tests\Fixture\TestModel;
use CCT\Component\Rest\Transformer\Response\ObjectCollectionTransformer;
use PHPUnit\Framework\TestCase;

class ObjectCollectionTransformerTest extends TestCase
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

    public function setup()
    {
        $this->metadataPath = realpath(__DIR__ . '/../../Fixture/Resources/metadata');
        $this->responseDirectory = realpath(__DIR__ . '/../../Fixture/Resources/Response/');
        $this->namespacePrefix = 'CCT\\Component\\Rest\\Tests\\Fixture';
    }

    public function testTransformResponseForArrayOfObject()
    {
        $response = new Response(
            '[{
                "heading": "heading 1",
                "body": "body 1"
            },
            {
                "heading": "heading 2",
                "body": "body 2"
            }]',
            200,
            [
                'Content-Type' => 'application/json'
            ]
        );

        $objectCollectionTransformer = new ObjectCollectionTransformer($this->createJMSSerializer(), TestModel::class, null);

        $objectCollectionTransformer->transform($response);
        $data = $response->getData();

        $this->assertTrue(is_array($data));
        $this->assertCount(2, $data);
        $this->assertInstanceOf(TestModel::class, current($data));
        $this->assertInstanceOf(TestModel::class, next($data));
    }

    public function testTransformResponseForArrayObjectWithKey()
    {
        $response = new Response(
            '{ "data" : [{
                "heading": "heading 1",
                "body": "body 1"
            },
            {
                "heading": "heading 2",
                "body": "body 2"
            }],
            "total": 2
            }',
            200,
            [
                'Content-Type' => 'application/json'
            ]
        );

        $objectCollectionTransformer = new ObjectCollectionTransformer($this->createJMSSerializer(), TestModel::class, null);
        $objectCollectionTransformer->setMappingKeys(['data']);

        $objectCollectionTransformer->transform($response);
        $data = $response->getData();

        $this->assertTrue(is_array($data));
        $this->assertArrayHasKey('data', $data);
        $this->assertCount(2, $data['data']);

        $this->assertInstanceOf(TestModel::class, current($data['data']));
        $this->assertInstanceOf(TestModel::class, next($data['data']));

        $this->assertArrayHasKey('total', $data);
    }

    public function testTransformResponseForArrayObjectWithMultipleKey()
    {
        $response = new Response(
            '{ "updated" : [{
                "heading": "heading 1",
                "body": "body 1"
            },
            {
                "heading": "heading 2",
                "body": "body 2"
            }],
            "deleted" : [{
                "heading": "heading 3",
                "body": "body 3"
            },
            {
                "heading": "heading 4",
                "body": "body 4"
            }],
            "total": 4
            }',
            200,
            [
                'Content-Type' => 'application/json'
            ]
        );

        $objectCollectionTransformer = new ObjectCollectionTransformer($this->createJMSSerializer(), TestModel::class, null);
        $objectCollectionTransformer->setMappingKeys(['updated', 'deleted']);

        $objectCollectionTransformer->transform($response);
        $data = $response->getData();

        $this->assertTrue(is_array($data));
        $this->assertArrayHasKey('updated', $data);
        $this->assertCount(2, $data['updated']);

        $this->assertInstanceOf(TestModel::class, current($data['updated']));
        $this->assertInstanceOf(TestModel::class, next($data['updated']));

        $this->assertArrayHasKey('deleted', $data);
        $this->assertCount(2, $data['deleted']);

        $this->assertInstanceOf(TestModel::class, current($data['deleted']));
        $this->assertInstanceOf(TestModel::class, next($data['deleted']));

        $this->assertArrayHasKey('total', $data);
    }

    public function testSupportReturnsTrueForSequentialArray()
    {
        $response = new Response(
            '[{
                "heading": "heading 1",
                "body": "body 1"
            },
            {
                "heading": "heading 2",
                "body": "body 2"
            }]',
            200,
            [
                'Content-Type' => 'application/json'
            ]
        );

        $objectCollectionTransformer = new ObjectCollectionTransformer($this->createJMSSerializer(), TestModel::class, null);

        $this->assertTrue(
            $objectCollectionTransformer->supports($response)
        );
    }

    public function testSupportReturnsFalseForHashArray()
    {
        $response = new Response(
            '{"data1" :[{
                "heading": "heading 1",
                "body": "body 1"
            }],
            "data2" :[{
                "heading": "heading 2",
                "body": "body 2"
            }]}',
            200,
            [
                'Content-Type' => 'application/json'
            ]
        );

        $objectCollectionTransformer = new ObjectCollectionTransformer($this->createJMSSerializer(), TestModel::class, null);

        $this->assertFalse(
            $objectCollectionTransformer->supports($response)
        );
    }

    public function testSupportReturnsFalseForMissingKey()
    {
        $response = new Response(
            '{"data1" :[{
                "heading": "heading 1",
                "body": "body 1"
            }]}',
            200,
            [
                'Content-Type' => 'application/json'
            ]
        );

        $objectCollectionTransformer = new ObjectCollectionTransformer($this->createJMSSerializer(), TestModel::class,
            null);

        $objectCollectionTransformer->setMappingKeys(['does-not-exist']);

        $this->assertFalse(
            $objectCollectionTransformer->supports($response)
        );
    }

    public function testSupportReturnsTrueForMatchingKeys()
    {
        $response = new Response(
            '{"data1" :[{
                "heading": "heading 1",
                "body": "body 1"
            }]
            }',
            200,
            [
                'Content-Type' => 'application/json'
            ]
        );

        $objectCollectionTransformer = new ObjectCollectionTransformer($this->createJMSSerializer(), TestModel::class,
            null);

        $objectCollectionTransformer->setMappingKeys(['data1']);

        $this->assertTrue(
            $objectCollectionTransformer->supports($response)
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

        $objectCollectionTransformer = new ObjectCollectionTransformer($this->createJMSSerializer(), TestModel::class, null);

        $this->assertFalse(
            $objectCollectionTransformer->supports($response)
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

        $objectCollectionTransformer = new ObjectCollectionTransformer($this->createJMSSerializer(), TestModel::class, null);

        $this->assertFalse(
            $objectCollectionTransformer->supports($response)
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
        $objectCollectionTransformer = new ObjectCollectionTransformer($this->createJMSSerializer(), TestModel::class, null);
        $this->assertFalse(
            $objectCollectionTransformer->supports($response)
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
