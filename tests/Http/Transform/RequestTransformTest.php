<?php

declare(strict_types=1);

namespace CCT\Component\Rest\Tests\Http\Transform;

use CCT\Component\Rest\Http\Transform\RequestTransform;
use CCT\Component\Rest\Http\Transform\ResponseTransform;
use CCT\Component\Rest\Transformer\Request\RequestTransformerInterface;
use PHPUnit\Framework\TestCase;

class RequestTransformTest extends TestCase
{
    public function testTransformConvertsObjectToArray()
    {
        $data = [
            'object' => [
                'name' => 'test'
            ]
        ];
        $transformer = $this->createConfiguredMock(
            RequestTransformerInterface::class,
            [
                'transform' => $data,
                'supports' => true
            ]
        );

        $requestTransform = new RequestTransform([$transformer]);
        $formData = $requestTransform->transform(['test' => 'Should be overridden']);

        $this->assertEquals($data, $formData);
    }

    public function testTransformSupportFalse()
    {
        $data = [
            'object' => [
                'name' => 'test'
            ]
        ];
        $transformer = $this->createConfiguredMock(
            RequestTransformerInterface::class,
            [
                'transform' => $data,
                'supports' => false
            ]
        );

        $requestTransform = new RequestTransform([$transformer]);
        $formDataToTransform = ['test' => 'Should be overridden'];
        $formData = $requestTransform->transform($formDataToTransform);

        $this->assertEquals($formDataToTransform, $formData);
    }

    public function testTransformEmptyArrayReturnsEmptyArray()
    {
        $data = [
            'object' => [
                'name' => 'test'
            ]
        ];
        $transformer = $this->createConfiguredMock(
            RequestTransformerInterface::class,
            [
                'transform' => $data,
                'supports' => true
            ]
        );

        $requestTransform = new RequestTransform([$transformer]);
        $formDataToTransform = [];
        $formData = $requestTransform->transform($formDataToTransform);

        $this->assertEmpty($formData);
    }
}
