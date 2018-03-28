<?php

declare(strict_types=1);

namespace CCT\Component\Rest\Tests\Transformer\Response;

use CCT\Component\Rest\Config;
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

    public function testTransformResponseForObject()
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

        $this->assertTrue(is_array($response->getData()));
//        $this->assertInstanceOf(TestModel::class, $response->getData());
//        $this->assertEquals('heading 1', $response->getData()->getHeading());
//        $this->assertEquals('body 1', $response->getData()->getBody());
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
