<?php

declare(strict_types=1);

namespace CCT\Component\Rest;

use CCT\Component\Rest\Exception\InvalidParameterException;
use CCT\Component\Rest\Http\RequestInterface;
use CCT\Component\Rest\Http\SerializerRequestInterface;
use CCT\Component\Rest\Http\Transform\RequestTransform;
use CCT\Component\Rest\Http\Transform\ResponseTransform;
use CCT\Component\Rest\Serializer\Context\Context;
use CCT\Component\Rest\Serializer\JMSSerializerBuilder;
use CCT\Component\Rest\Transformer\Request\FormObjectTransformer;
use CCT\Component\Rest\Transformer\Response\ObjectCollectionTransformer;
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

    /**
     * AbstractClient constructor.
     *
     * @param Config $config
     * @param bool $defaultConfig
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(Config $config, bool $defaultConfig = true)
    {
        if (false === $config->has(Config::ENDPOINT)) {
            throw new \InvalidArgumentException(
                sprintf('Configuration key %s is missing', Config::ENDPOINT)
            );
        }

        $this->defaultConfig = $defaultConfig;
        $this->config = $config;
        $this->client = new GuzzleClient([
            'base_uri' => $config->get(Config::ENDPOINT)
        ]);

        if ($defaultConfig) {
            $this->applyDefaults();
        }
    }

    /**
     * Enable Default Config
     */
    public function enableDefaultConfig(): void
    {
        $this->defaultConfig = true;
    }

    /**
     * Disabled default config
     */
    public function disableDefaultConfig(): void
    {
        $this->defaultConfig = false;
    }

    /**
     * Is default config enabled
     *
     * @return bool
     */
    public function isDefaultConfig(): bool
    {
        return $this->defaultConfig;
    }

    /**
     * Applies the default config
     *
     * @return mixed
     */
    abstract protected function applyDefaults();

    /**
     * Clears Config values
     */
    public function clearDefaults(): void
    {
        $this->config->remove(Config::METADATA_DIRS);
        $this->config->remove(Config::DEBUG);
        $this->config->remove(Config::EVENT_SUBSCRIBERS);
        $this->config->remove(Config::SERIALIZATION_HANDLERS);
        $this->config->remove(Config::OBJECT_CONSTRUCTOR);
        $this->config->remove(Config::USE_DEFAULT_RESPONSE_TRANSFORMERS);
    }

    /**
     * @param Config $config
     *
     * @return SerializerInterface|null
     * @throws \JMS\Serializer\Exception\RuntimeException
     * @throws \JMS\Serializer\Exception\InvalidArgumentException
     */
    protected function buildSerializer(Config $config): ?SerializerInterface
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

    /**
     * Gets the built serializer
     *
     * @param Config $config
     *
     * @return SerializerInterface|null
     * @throws \JMS\Serializer\Exception\RuntimeException
     * @throws \JMS\Serializer\Exception\InvalidArgumentException
     */
    protected function getBuiltSerializer(Config $config): ?SerializerInterface
    {
        if (null === static::$serializer) {
            static::$serializer = $this->buildSerializer($config);
        }

        return static::$serializer;
    }

    /**
     * @param $class
     * @param Config $config
     * @param SerializerInterface|null $serializer
     *
     * @return object
     * @throws \ReflectionException
     * @throws \CCT\Component\Rest\Exception\InvalidParameterException
     */
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

    /**
     * @param Config $config
     * @param SerializerInterface $serializer
     * @param $modelClass
     */
    protected function applyDefaultResponseTransformers(
        Config $config,
        SerializerInterface $serializer,
        $modelClass
    ): void {
        $config->set(Config::RESPONSE_TRANSFORMERS, [
            new ObjectTransformer($serializer, $modelClass, new Context()),
            new ObjectCollectionTransformer($serializer, $modelClass, new Context())
        ]);
    }

    /**
     * @param Config $config
     * @param SerializerInterface $serializer
     */
    protected function applyDefaultRequestTransformers(Config $config, SerializerInterface $serializer): void
    {
        $config->set(Config::REQUEST_TRANSFORMERS, [
            new FormObjectTransformer($serializer, new Context()),
        ]);
    }

    /**
     * @param Config $config
     *
     * @return RequestTransform|null
     */
    protected function createRequestTransform(Config $config): ?RequestTransform
    {
        return new RequestTransform(
            $config->get(Config::REQUEST_TRANSFORMERS, [])
        );
    }

    /**
     * @param Config $config
     *
     * @return ResponseTransform|null
     */
    protected function createResponseTransform(Config $config): ?ResponseTransform
    {
        return new ResponseTransform(
            $config->get(Config::RESPONSE_TRANSFORMERS, [])
        );
    }
}
