<?php

declare(strict_types=1);

use CCT\Component\Rest\Serializer\Context\Context;
use CCT\Component\Rest\Serializer\JMSSerializerAdapter;
use JMS\Serializer\ContextFactory\DeserializationContextFactoryInterface;
use JMS\Serializer\ContextFactory\SerializationContextFactoryInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exclusion\ExclusionStrategyInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use PhpCollection\MapInterface;
use PHPUnit\Framework\TestCase;

class JMSSerializerAdapterTest extends TestCase
{
    private $serializer;

    private $serializationContextFactory;

    private $deserializationContextFactory;

    private $adapter;

    protected function setUp()
    {
        if (!class_exists('JMS\Serializer\Serializer')) {
            $this->markTestSkipped('JMSSerializer is not installed.');
        }

        $this->serializer = $this->getMockBuilder(Serializer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->serializationContextFactory = $this
            ->getMockBuilder(SerializationContextFactoryInterface::class)->getMock();

        $this->deserializationContextFactory = $this
            ->getMockBuilder(DeserializationContextFactoryInterface::class)->getMock();

        $this->adapter = new JMSSerializerAdapter(
            $this->serializer,
            $this->serializationContextFactory,
            $this->deserializationContextFactory
        );
    }

    public function testSerializeWithoutContextFactories()
    {
        $jmsContext = SerializationContext::create();
        $adapter = new JMSSerializerAdapter($this->serializer);

        $this->serializer->expects($this->once())->method('serialize')->with('foo', 'json', $jmsContext);

        $adapter->serialize('foo', 'json', new Context());
    }

    public function testDeSerializeWithoutContextFactories()
    {
        $jmsContext = DeserializationContext::create();
        $adapter = new JMSSerializerAdapter($this->serializer);

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with('foo', 'string', 'json', $jmsContext)
        ;

        $adapter->deserialize('foo', 'string', 'json', new Context());
    }

    public function testSerializeAdapter()
    {
        $jmsContext = $this->getMockBuilder(SerializationContext::class)->getMock();

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with('foo', 'json', $jmsContext);

        $this->serializationContextFactory->expects($this->once())->method('createSerializationContext')
            ->willReturn($jmsContext);

        $this->adapter->serialize('foo', 'json', new Context());
    }

    public function testBasicDeserializeAdapter()
    {
        $jmsContext = $this->getMockBuilder(DeserializationContext::class)->getMock();

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with('foo', 'string', 'json', $jmsContext);

        $this->deserializationContextFactory->expects($this->once())->method('createDeserializationContext')
            ->willReturn($jmsContext);
        $this->adapter->deserialize('foo', 'string', 'json', new Context());
    }

    public function testContextInfoAreConverted()
    {
        $exclusion = $this->getMockBuilder(ExclusionStrategyInterface::class)->getMock();
        $jmsContext = $this->getMockBuilder(SerializationContext::class)->getMock();
        $jmsContext->attributes = $this->getMockBuilder(MapInterface::class)->getMock();
        $jmsContext->expects($this->once())->method('setGroups')->with(['foo']);
        $jmsContext->expects($this->once())->method('setSerializeNull')->with(true);
        $jmsContext->expects($this->once())->method('enableMaxDepthChecks');
        $jmsContext->expects($this->once())->method('setVersion')->with('5.0.1');
        $jmsContext->expects($this->once())->method('addExclusionStrategy')->with($exclusion);

        $jmsContext->attributes->expects($this->once())->method('set')->with('foo', 'bar');

        $this->serializationContextFactory->method('createSerializationContext')->willReturn($jmsContext);

        $restContext = new Context();
        $restContext->setAttribute('foo', 'bar');
        $restContext->setGroups(['foo']);
        $restContext->setSerializeNull(true);
        $restContext->setVersion('5.0.1');
        $restContext->enableMaxDepth();
        $restContext->addExclusionStrategy($exclusion);

        $this->adapter->serialize('foo', 'json', $restContext);
    }
}
