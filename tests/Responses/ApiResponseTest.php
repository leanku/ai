<?php

namespace Leanku\Ai\Tests\Responses;

use Leanku\Ai\Responses\ApiResponse;
use Leanku\Ai\Tests\TestCase;

class ApiResponseTest extends TestCase
{
    public function testApiResponseCreation()
    {
        $response = new ApiResponse(
            content: 'Test content',
            role: 'assistant',
            model: 'gpt-4',
            usage: ['tokens' => 100],
            data: ['original' => 'data'],
            successful: true,
            finishReason: 'stop'
        );

        $this->assertEquals('Test content', $response->getContent());
        $this->assertEquals('assistant', $response->getRole());
        $this->assertEquals('gpt-4', $response->getModel());
        $this->assertEquals(['tokens' => 100], $response->getUsage());
        $this->assertEquals(['original' => 'data'], $response->getData());
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('stop', $response->getFinishReason());
    }

    public function testUnsuccessfulResponse()
    {
        $response = new ApiResponse(
            content: '',
            role: 'assistant',
            model: 'gpt-4',
            usage: [],
            data: [],
            successful: false
        );

        $this->assertFalse($response->isSuccessful());
    }
}