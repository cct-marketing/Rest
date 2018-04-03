<?php

declare(strict_types=1);

namespace CCT\Component\Rest\Tests\Serializer\Mapping\Loader;

use CCT\Component\Rest\Serializer\Mapping\Loader\DirectoryLoader;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
use Symfony\Component\Serializer\Mapping\Loader\XmlFileLoader;
use Symfony\Component\Serializer\Mapping\Loader\YamlFileLoader;

class DirectoryLoaderTest extends TestCase
{
    /**
     * @var DirectoryLoader
     */
    protected $directoryLoader;

    /**
     * Temp directory
     * @var string
     */
    protected $tempDir;

    public function setUp()
    {
        parent::setUp();

        $this->directoryLoader = new DirectoryLoader();
    }

    public function testLoadWithNonExistentDiretoryShouldThrowInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->directoryLoader->load(__DIR__ . 'NONEXISTENTDIRECTORY');
    }

    public function testLoadWithUnWritableDiretoryShouldThrowInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);

        $unWriteableDirectory = $this->createTemporaryDirectory('un-writeable', 0333);

        $this->directoryLoader->load($unWriteableDirectory);
    }

    public function testLoadWithEmptyDirectoryShouldReturnEmptyArray()
    {
        $emptyDirectory = $this->createTemporaryDirectory('empty');

        $loaders = $this->directoryLoader->load($emptyDirectory);

        $this->assertTrue(is_array($loaders));
    }

    public function testLoadWithIncompatiableTypeShouldThrowException()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->directoryLoader->load(__DIR__ . '/TestFiles', 'txt');
    }

    protected function createTemporaryDirectory($dir, $mode = 0777)
    {
        $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $dir;
        if (!mkdir($this->tempDir, $mode)) {
            throw new \Exception('Opps!! could not create directory');
        }

        return $this->tempDir;
    }

    public function testLoadWithYamlTypeShouldReturnArray()
    {

        $loaders = $this->directoryLoader->load(__DIR__ . '/TestFiles', DirectoryLoader::FILE_TYPE_YAML);

        $this->assertTrue(is_array($loaders));
        $this->assertCount(2, $loaders);
        $this->assertInstanceOf(YamlFileLoader::class, $loaders[0]);
        $this->assertInstanceOf(YamlFileLoader::class, $loaders[1]);
    }

    public function testLoadWithXmlTypeShouldReturnArray()
    {

        $loaders = $this->directoryLoader->load(__DIR__ . '/TestFiles', DirectoryLoader::FILE_TYPE_XML);

        $this->assertTrue(is_array($loaders));
        $this->assertCount(3, $loaders);
        $this->assertInstanceOf(XmlFileLoader::class, $loaders[0]);
        $this->assertInstanceOf(XmlFileLoader::class, $loaders[1]);
        $this->assertInstanceOf(XmlFileLoader::class, $loaders[2]);
    }

    public function tearDown()
    {
        parent::tearDown();

        if (null !== $this->tempDir && file_exists($this->tempDir)) {
            if(!is_writeable($this->tempDir)){
                chmod($this->tempDir, 0777);
            }
            rmdir($this->tempDir);
        }
    }
}
