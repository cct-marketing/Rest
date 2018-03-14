<?php

namespace CCT\Component\Rest\Serializer\Mapping\Loader;

use InvalidArgumentException;
use Symfony\Component\Serializer\Mapping\Loader\XmlFileLoader;
use Symfony\Component\Serializer\Mapping\Loader\YamlFileLoader;

/**
 * Load recursively all YAML or XML configuration files in services directories
 */
class DirectoryLoader
{
    const FILE_TYPE_YAML = 'yaml';

    const FILE_TYPE_XML = 'xml';

    /**
     * @param $dir
     * @param string $type supported types are DirectoryLoader::FILE_TYPE_YAML or DirectoryLoader::FILE_TYPE_XML
     *
     * @return array
     */
    public function load($dir, $type = self::FILE_TYPE_YAML)
    {
        $loaders = [];

        $dir = rtrim($dir, DIRECTORY_SEPARATOR);

        if (!is_dir($dir)) {
            throw new InvalidArgumentException(sprintf('The directory "%s" does not exist.', $dir));
        }

        foreach (scandir($dir) as $file) {
            if (in_array($file, array(".", ".."))) {
                continue;
            }

            if ($type !== strtolower(pathinfo($file, PATHINFO_EXTENSION))) {
                continue;
            }

            if (!is_readable($dir . DIRECTORY_SEPARATOR . $file)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'The file "%s" is not readable.',
                        $dir . DIRECTORY_SEPARATOR . $file
                    )
                );
            }

            $loaders[] = $this->createLoader($dir . DIRECTORY_SEPARATOR . $file, $type);
        }
        return $loaders;
    }

    /**
     * Create config loaders
     *
     * @param $file
     * @param $type
     *
     * @return XmlFileLoader|YamlFileLoader
     */
    private function createLoader($file, $type)
    {
        if (self::FILE_TYPE_YAML === $type) {
            return new YamlFileLoader($file);
        }

        if (self::FILE_TYPE_XML === $type) {
            return new XmlFileLoader($file);
        }

        throw new InvalidArgumentException(sprintf('The file type "%s" is not supported.', $type));
    }
}
