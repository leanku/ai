<?php

namespace Leanku\Ai\Tests\Responses\Adaptors;

use Leanku\Ai\Requests\Adaptors\OpenAIAdaptor;
use Leanku\Ai\Tests\TestCase;
use GuzzleHttp\Psr7\Response;

class OpenAIAdaptorTest extends TestCase
{
    public function testAdaptSuccess()
    {
        $responseData = $this->getMockResponse('Test response content');
        $response = new Response(200, [], json_encode($responseData));

        $adapted = OpenAIAdaptor::adapt($response);

        $this->assertEquals('Test response content', $adapted->getContent());
        $this->assertEquals('assistant', $adapted->getRole());
        $this->assertEquals('gpt-3.5-turbo-0613', $adapted->getModel());
        $this->assertEquals([
            'prompt_tokens' => 9,
            'completion_tokens' => 12,
            'total_tokens' => 21,
        ], $adapted->getUsage());
        $this->assertTrue($adapted->isSuccessful());
        $this->assertEquals('stop', $adapted->getFinishReason());
    }

    public function testAdaptStream()
    {
        $chunk = $this->getMockStreamResponse();

        $adapted = OpenAIAdaptor::adaptStream($chunk);

        $this->assertNotNull($adapted);
        $this->assertEquals('Hello', $adapted->getContent());
        $this->assertEquals('assistant', $adapted->getRole());
    }

    public function testAdaptStreamWithEmptyContent()
    {
        $chunk = [
            'choices' => [
                [
                    'delta' => [
                        'role' => 'assistant',
                    ],
                ],
            ],
        ];

        $adapted = OpenAIAdaptor::adaptStream($chunk);

        $this->assertNotNull($adapted);
        $this->assertEquals('', $adapted->getContent());
        $this->assertEquals('assistant', $adapted->getRole());
    }

    public function testAdaptStreamReturnsNullForInvalidChunk()
    {
        $chunk = ['choices' => []]; // 无效的块

        $adapted = OpenAIAdaptor::adaptStream($chunk);

        $this->assertNull($adapted);
    }

    public function testAdaptThrowsExceptionOnInvalidResponse()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid OpenAI response format');

        $invalidResponse = new Response(200, [], json_encode([
            'invalid' => 'response',
        ]));

        OpenAIAdaptor::adapt($invalidResponse);
    }
}