<?php

namespace CCT\Component\Rest\Tests\Fixture;

use CCT\Component\Rest\AbstractClient;
use CCT\Component\Rest\Config;

class TestClient extends AbstractClient
{
    /**
     * @return ScrapeRequest
     */
    public function apiTest(): TestRequest
    {
        $config = clone $this->config;
        $modelClass = TestModel::class;

        $serializer = $this->getBuiltSerializer($config);
        if ($this->shouldUseDefaultResponseTransformers() && null !== $serializer) {
            $this->applyDefaultResponseTransformers($config, $serializer, $modelClass);
        }

        return $this->createRequestInstance(TestRequest::class, $config, null);
    }

    protected function applyDefaults()
    {
        $this->config->set(Config::METADATA_DIRS, [
            [
                'dir' => __DIR__ . '/Resources/metadata',
                'namespacePrefix' => 'CCT\\Component\\Rest\\Tests\\Fixture',
            ]
        ]);

        $this->config->set(Config::USE_DEFAULT_RESPONSE_TRANSFORMERS, true);
    }
}
