<?php

declare(strict_types=1);

namespace CCT\Component\Rest;

use Assert\Assert;
use CCT\Component\Rest\Exception\InvalidParameterException;
use CCT\Component\Rest\Http\RequestInterface;
use CCT\Component\Rest\Http\SerializerRequestInterface;
use CCT\Component\Rest\Http\Transform\RequestTransform;
use CCT\Component\Rest\Http\Transform\ResponseTransform;
use CCT\Component\Rest\Serializer\Context\Context;
use CCT\Component\Rest\Serializer\JMSSerializerBuilder;
use CCT\Component\Rest\Transformer\Request\FormObjectTransformer;
use CCT\Component\Rest\Transformer\Response\CollectionObjectTransformer;
use CCT\Component\Rest\Transformer\Response\ObjectTransformer;
use GuzzleHttp\Client as GuzzleClient;
use CCT\Component\Rest\Serializer\SerializerInterface;

abstract class AbstractClient
{
    /**
     * @var GuzzleClient
     */
    protected $client;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var bool
     */
    protected $defaultConfig = true;

    /**
     * @var SerializerInterface
     */
    protected static $serializer;

    public function __construct(Config $config, bool $defaultConfig = true)
    {
        Assert::that($config->toArray())->keyExists(Config::ENDPOINT);

        $this->defaultConfig = $defaultConfig;
        $this->config = $config;
        $this->client = new GuzzleClient([
            'base_uri' => $config->get(Config::ENDPOINT)
        ]);

        if ($defaultConfig) {
            $this->applyDefaults();
        }
    }

    public function enableDefaultConfig()
    {
        $this->defaultConfig = true;
    }

    public function disableDefaultConfig()
    {
        $this->defaultConfig = false;
    }

    public function isDefaultConfig(): bool
    {
        return $this->defaultConfig;
    }

    abstract protected function applyDefaults();

    public function clearDefaults()
    {
        $this->config->remove(Config::METADATA_DIRS);
        $this->config->remove(Config::DEBUG);
        $this->config->remove(Config::EVENT_SUBSCRIBERS);
        $this->config->remove(Config::SERIALIZATION_HANDLERS);
        $this->config->remove(Config::OBJECT_CONSTRUCTOR);
        $this->config->remove(Config::USE_DEFAULT_RESPONSE_TRANSFORMERS);
    }

    protected function buildSerializer(Config $config)
    {

        if (class_exists('JMS\Serializer\Serializer')) {
            return JMSSerializerBuilder::createByConfig($config)
                ->configureDefaults()
                ->build();
        }

        if (class_exists('Symfony\Component\Serializer\Serializer')) {
            return JMSSerializerBuilder::createByConfig($config)
                ->configureDefaults()
                ->build();
        }

        return null;
    }

    protected function getBuiltSerializer(Config $config)
    {
        if (null === static::$serializer) {
            static::$serializer = $this->buildSerializer($config);
        }

        return static::$serializer;
    }

    protected function createRequestInstance($class, Config $config, SerializerInterface $serializer = null)
    {
        $reflectionClass = new \ReflectionClass($class);

        if (!$reflectionClass->implementsInterface(RequestInterface::class)) {
            throw new InvalidParameterException(sprintf(
                'The class must be an instance of %s',
                RequestInterface::class
            ));
        }

        if (!$reflectionClass->implementsInterface(SerializerRequestInterface::class)) {
            return $reflectionClass->newInstance(
                $this->client
            );
        }

        return $reflectionClass->newInstance(
            $this->client,
            $config,
            $serializer,
            $this->createRequestTransform($config),
            $this->createResponseTransform($config)
        );
    }

    /**
     * Should use the default response transformers?
     *
     * @return bool
     */
    protected function shouldUseDefaultResponseTransformers(): bool
    {
        return (bool)$this->config->get(Config::USE_DEFAULT_RESPONSE_TRANSFORMERS, true);
    }

    protected function applyDefaultResponseTransformers(Config $config, SerializerInterface $serializer, $modelClass)
    {
        $config->set(Config::RESPONSE_TRANSFORMERS, [
            new ObjectTransformer($serializer, $modelClass, new Context()),
            new CollectionObjectTransformer($serializer, $modelClass, new Context())
        ]);
    }

    protected function applyDefaultRequestTransformers(Config $config, SerializerInterface $serializer)
    {
        $config->set(Config::REQUEST_TRANSFORMERS, [
            new FormObjectTransformer($serializer, new Context()),
        ]);
    }

    protected function createRequestTransform(Config $config): ?RequestTransform
    {
        return new RequestTransform(
            $config->get(Config::REQUEST_TRANSFORMERS, [])
        );
    }

    protected function createResponseTransform(Config $config): ?ResponseTransform
    {
        return new ResponseTransform(
            $config->get(Config::RESPONSE_TRANSFORMERS, [])
        );
    }
}
