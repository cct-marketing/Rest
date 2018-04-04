<?php

namespace CCT\Component\Rest\Serializer;

use CCT\Component\Rest\Config;
use CCT\Component\Rest\Serializer\Mapping\Loader\DirectoryLoader;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\LoaderChain;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SymfonySerializerBuilder implements SerializerBuilderInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var array|EncoderInterface[]
     */
    private $encoders;

    /**
     * @var array|NormalizableInterface[]
     */
    private $normalizers;

    /**
     * SymfonySerializerBuilder constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Create instance by static method
     *
     * @param Config $config
     *
     * @return static
     */
    public static function createByConfig(Config $config)
    {
        return new static($config);
    }

    /**
     * Set default configuration
     *
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\Serializer\Exception\RuntimeException
     * @throws \Symfony\Component\Serializer\Exception\MappingException
     */
    public function configureDefaults()
    {
        $this->encoders = array(new JsonEncoder());

        $loaders = $this->generateLoaders();

        $classMetadataFactory = new ClassMetadataFactory(new LoaderChain($loaders));

        $normalizer = new ObjectNormalizer($classMetadataFactory, new CamelCaseToSnakeCaseNameConverter());
        $this->normalizers = array($normalizer);

        return $this;
    }

    /**
     * Generates loaders from directory config
     *
     * @return array
     *
     * @throws \Symfony\Component\Serializer\Exception\MappingException
     * @throws \InvalidArgumentException
     */
    protected function generateLoaders(): array
    {
        $loadersCollection = [];
        $directoryLoader = new DirectoryLoader();

        $metadataDirs = $this->config->get(Config::METADATA_DIRS, []);
        foreach ($metadataDirs as $metadataDir) {
            $loaders = $directoryLoader->load($metadataDir['dir']);
            if (empty($loaders)) {
                continue;
            }

            array_push($loadersCollection, ...$loaders);
        }

        return $loadersCollection;
    }

    /**
     * Builds the Symfony Serializer object.
     *
     * @return SerializerInterface
     */
    public function build(): SerializerInterface
    {
        $serializer = new Serializer($this->normalizers, $this->encoders);

        return new SymfonySerializerAdapter($serializer);
    }

    /**
     * @return array|EncoderInterface[]
     */
    public function getEncoders(): array
    {
        return $this->encoders;
    }

    /**
     * @param array $encoders
     */
    public function setEncoders(array $encoders): void
    {
        $this->encoders = $encoders;
    }

    /**
     * @return array|NormalizableInterface[]
     */
    public function getNormalizers(): array
    {
        return $this->normalizers;
    }

    /**
     * @param array $normalizers
     */
    public function setNormalizers(array $normalizers): void
    {
        $this->normalizers = $normalizers;
    }

    /**
     * Add directory for metadata config
     *
     * @param $dir
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addMetadataDir($dir): self
    {
        if (!is_dir($dir)) {
            throw new \InvalidArgumentException(sprintf('The directory "%s" does not exist.', $dir));
        }

        return $this;
    }
}
