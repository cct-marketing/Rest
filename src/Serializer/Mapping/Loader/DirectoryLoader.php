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
        $dir = rtrim($dir, DIRECTORY_SEPARATOR);

        if (!is_dir($dir)) {
            throw new InvalidArgumentException(sprintf('The directory "%s" does not exist.', $dir));
        }

        return $this->scanDirectory($dir, $type);
    }

    /**
     * Scan directory for files matching extention type
     *
     * @param string $dir
     * @param string $type
     *
     * @return array
     */
    private function scanDirectory($dir, $type)
    {
        $loaders = [];

        foreach (scandir($dir) as $file) {
            if (false === $this->checkFileType($file, $type)) {
                continue;
            }

            $this->isReadable($dir, $file);

            $loaders[] = $this->createLoader($dir . DIRECTORY_SEPARATOR . $file, $type);
        }

        return $loaders;
    }

    /**
     * Checks if file extension matches type
     *
     * @param string $file
     * @param string $type
     *
     * @return bool
     */
    private function checkFileType($file, $type)
    {
        if (in_array($file, array(".", ".."))
            || $type !== strtolower(pathinfo($file, PATHINFO_EXTENSION))
        ) {
            return false;
        }

        return true;
    }

    /**
     * Is file readable
     *
     * @param string $dir
     * @param string $file
     *
     * @return bool
     */
    private function isReadable($dir, $file)
    {
        if (!is_readable($dir . DIRECTORY_SEPARATOR . $file)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The file "%s" is not readable.',
                    $dir . DIRECTORY_SEPARATOR . $file
                )
            );
        }

        return true;
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
