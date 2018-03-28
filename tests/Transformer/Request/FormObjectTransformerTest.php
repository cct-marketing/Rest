<?php

declare(strict_types=1);

namespace CCT\Component\Rest\Tests\Transformer\Request;

use CCT\Component\Rest\Config;
use CCT\Component\Rest\Serializer\Context\Context;
use CCT\Component\Rest\Serializer\JMSSerializerBuilder;
use CCT\Component\Rest\Serializer\SerializerInterface;
use CCT\Component\Rest\Tests\Fixture\TestModel;
use CCT\Component\Rest\Transformer\Request\FormObjectTransformer;
use PHPUnit\Framework\TestCase;

class FormObjectTransformerTest extends TestCase
{

    public function setup()
    {
        $this->metadataPath = realpath(__DIR__ . '/../../Fixture/Resources/metadata');
        $this->responseDirectory = realpath(__DIR__ . '/../../Fixture/Resources/Response/');
        $this->namespacePrefix = 'CCT\\Component\\Rest\\Tests\\Fixture';
    }

    public function testTransformObjectToArray(){

        $formObjectTransformer = new FormObjectTransformer($this->createJMSSerializer(), null);
        $testObject = new TestModel();
        $testObject->setHeading('Good morning tester!');
        $result = $formObjectTransformer->transform($testObject);

        $this->assertTrue(is_array($result));
        $this->assertArrayHasKey('heading',$result);
        $this->assertEquals('Good morning tester!', $result['heading']);
    }

    public function testTransformArrayWithObjectToArray(){

        $formObjectTransformer = new FormObjectTransformer($this->createJMSSerializer(), null);
        $testObject = new TestModel();
        $testObject->setHeading('Good morning tester!');
        $testObject->setBody('What a morning');
        $testArray = [
            'test' => $testObject,
            'field2' => 'Howdy'
        ];

        $result = $formObjectTransformer->transform($testArray);

        $this->assertTrue(is_array($result));
        $this->assertArrayHasKey('test',$result);
        $this->assertArrayHasKey('heading', $result['test']);
        $this->assertEquals('Good morning tester!', $result['test']['heading']);
    }

    public function testTransformArrayWithStdObjectToArray(){

        $formObjectTransformer = new FormObjectTransformer($this->createJMSSerializer(), null);
        $stdClass = new \stdClass();
        $stdClass->message = '¡Buen probador de la mañana!';
        $testArray = [
            'test' => $stdClass,
            'language' => 'Spanish'
        ];

        $result = $formObjectTransformer->transform($testArray);

        $this->assertTrue(is_array($result));
        $this->assertArrayHasKey('test',$result);
        $this->assertArrayHasKey('message', $result['test']);
        $this->assertEquals('¡Buen probador de la mañana!', $result['test']['message']);
    }

    public function testTransformArrayWithObjectAndContextReadToArray(){

        $context = new Context();
        $context->setGroups(['read']);

        $formObjectTransformer = new FormObjectTransformer($this->createJMSSerializer(), $context);
        $testObject = new TestModel();
        $testObject->setHeading('Good morning tester!');
        $testObject->setBody('What a morning');

        $testArray = [
            'test' => $testObject,
            'field2' => 'Howdy'
        ];

        $result = $formObjectTransformer->transform($testArray);

        $this->assertTrue(is_array($result));
        $this->assertArrayHasKey('test',$result);
        $this->assertArrayHasKey('heading', $result['test']);
        $this->assertArrayNotHasKey('body', $result['test']);
    }

    public function testSupportReturnsTrueForObject()
    {
        $formObjectTransformer = new FormObjectTransformer($this->createJMSSerializer(), null);

        $testObject = new TestModel();

        $this->assertTrue(
            $formObjectTransformer->supports($testObject)
        );
    }

    public function testSupportReturnsTrueForArray()
    {
        $formObjectTransformer = new FormObjectTransformer($this->createJMSSerializer(), null);

        $this->assertTrue(
            $formObjectTransformer->supports([])
        );
    }

    public function testSupportReturnsFalseForNull()
    {
        $formObjectTransformer = new FormObjectTransformer($this->createJMSSerializer(), null);

        $this->assertFalse(
            $formObjectTransformer->supports(null)
        );
    }

    public function testSupportReturnsFalseForString()
    {
        $formObjectTransformer = new FormObjectTransformer($this->createJMSSerializer(), null);

        $this->assertFalse(
            $formObjectTransformer->supports('string')
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
