<?php

declare(strict_types=1);

use CCT\Component\Rest\Serializer\Context\Context;
use CCT\Component\Rest\Serializer\JMSSerializerAdapter;
use CCT\Component\Rest\Serializer\SymfonySerializerAdapter;

use PhpCollection\MapInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SymfonySerializerAdapterTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $serializer;

    private $adapter;

    /**
     * @var MockObject
     */
    private $jsonEncoder;

    /**
     * @var MockObject
     */
    private $normalizer;

    protected function setUp()
    {
        if (!class_exists('Symfony\Component\Serializer\Serializer')) {
            $this->markTestSkipped('Symfony Serializer is not installed.');
        }

        $this->jsonEncoder = $this->getMockBuilder(JsonEncoder::class)
            //->setMethods([''])
            ->getMock();

        $this->normalizer = $this->getMockBuilder(ObjectNormalizer::class)
            ->getMock();

        $encoders = array($this->jsonEncoder);
        $normalizers = array($this->normalizer);

        $this->serializer = $this->getMockBuilder(Serializer::class)
            ->setConstructorArgs([$normalizers, $encoders])
            ->setMethods(null)
            ->getMock();


        $this->adapter = new SymfonySerializerAdapter(
            $this->serializer
        );
    }

    public function testSerializeWithContextObjectShouldConvertToArray()
    {
        $context = [
            'version' => null,
            'maxDepth' => null,
            'enable_max_depth' => null,
            'serializeNull' => null,
        ];

        $adapter = new SymfonySerializerAdapter($this->serializer);

        $this->jsonEncoder->method('supportsEncoding')
            ->willReturn(true);

        $this->jsonEncoder->expects($this->once())
            ->method('encode')
            ->with('foo', 'json', $context);

        $adapter->serialize('foo', 'json', Context::create());
    }

    public function testDeserializeWithContextObjectShouldConvertToArray()
    {
        $context = [
            'version' => null,
            'maxDepth' => null,
            'enable_max_depth' => null
        ];

        $adapter = new SymfonySerializerAdapter($this->serializer);

        $this->jsonEncoder->method('supportsDecoding')
            ->willReturn(true);

        $this->normalizer->method('supportsDenormalization')
            ->willReturn(true);

        $this->jsonEncoder->expects($this->once())
            ->method('decode')
            ->with('foo', 'json', $context);

        $adapter->deserialize('foo', 'string', 'json', new Context());
    }

    public function testContextInfoAreConverted()
    {

        $encoders = array(new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $serializer = new Serializer($normalizers, $encoders);

        $adapter = new SymfonySerializerAdapter(
            $serializer
        );

        $restContext = new Context();
        $restContext->setAttribute('foo', 'bar');
        $restContext->setGroups(['foo']);
        $restContext->setSerializeNull(true);
        $restContext->setVersion('5.0.1');
        $restContext->enableMaxDepth();

        $result = $adapter->serialize(['test'=> 'foo'], 'json', $restContext);

        $this->assertEquals('{"test":"foo"}', $result);
    }
}
