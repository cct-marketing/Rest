<?php

declare(strict_types=1);

namespace CCT\Component\Rest\Tests\Http\Transform;

use CCT\Component\Rest\Http\Response;
use CCT\Component\Rest\Http\Transform\RequestTransform;
use CCT\Component\Rest\Http\Transform\ResponseTransform;
use CCT\Component\Rest\Transformer\Request\RequestTransformerInterface;
use CCT\Component\Rest\Transformer\Response\ResponseTransformerInterface;
use PHPUnit\Framework\TestCase;

class ResponseTransformTest extends TestCase
{
    public function testTransformConvertsObjectToArray()
    {
        $data = [
            'object' => [
                'name' => 'test'
            ]
        ];

        $response = $this->createOkResponse(
            '{"data1":[{"heading":"heading 1","body":"body 1"}]}'
        );

        $responseTransform = new ResponseTransform([$this->createTransformerMock($data)]);
        $responseTransform->transform($response);

        $this->assertEquals($data, $response->getData());
    }

    public function testTransformSupportFalseDoesNotChangeContent()
    {
        $data = [
            'object' => [
                'name' => 'test'
            ]
        ];

        $response = $this->createOkResponse(
            '{"data1":[{"heading":"heading 1","body":"body 1"}]}'
        );

        $responseTransform = new ResponseTransform([$this->createTransformerMock($data, false)]);
        $responseTransform->transform($response);

        $this->assertEquals(json_decode($response->getContent(), true), $response->getData());
    }

    public function testTransformEmptyContentReturnsNull()
    {
        $data = [
            'object' => [
                'name' => 'test'
            ]
        ];
        $response = $this->createOkResponse(
            ''
        );

        $responseTransform = new ResponseTransform([$this->createTransformerMock($data)]);
        $responseTransform->transform($response);

        $this->assertNull($response->getData());
    }

    protected function createTransformerMock($data, $supports = true)
    {
        $transformer = $this->createConfiguredMock(
            ResponseTransformerInterface::class,
            [
                'supports' => $supports
            ]
        );
        $transformer->expects($this->any())
            ->method('transform')
            ->will(
                $this->returnCallback(
                    function (Response $response) use ($data) {
                        return $response->setData($data);
                    }
                )
            );

        return $transformer;
    }

    protected function createOkResponse($content)
    {
        return new Response(
            $content,
            200,
            [
                'Content-Type' => 'application/json'
            ]
        );
    }

    public function testTransformSupportsClosures()
    {

        $response = $this->createOkResponse(
            '{"data1":[{"heading":"heading 1","body":"body 1"}]}'
        );

        $transformer = function ($response) {
            $response->setData(['Closure called']);
        };

        $responseTransform = new ResponseTransform([$transformer]);
        $responseTransform->transform($response);

        $this->assertEquals(['Closure called'], $response->getData());
    }
}
