<?php

namespace Leanku\Ai\Tests\Requests;

use Leanku\Ai\Requests\ChatCompletionRequest;
use Leanku\Ai\Tests\TestCase;

class ChatCompletionRequestTest extends TestCase
{
    public function testChatCompletionRequestCreation()
    {
        $messages = [
            ['role' => 'user', 'content' => 'Hello'],
        ];

        $request = new ChatCompletionRequest($messages, 'gpt-4', 0.8, 1000);

        $this->assertEquals($messages, $request->getMessages());
        $this->assertEquals('gpt-4', $request->getModel());
        $this->assertEquals(0.8, $request->getTemperature());
        $this->assertEquals(1000, $request->getMaxTokens());
        $this->assertFalse($request->isStream());
    }

    public function testFluentSetters()
    {
        $request = new ChatCompletionRequest([], null);

        $request
            ->withModel('gpt-3.5-turbo')
            ->withTemperature(0.5)
            ->withMaxTokens(500)
            ->withStream(true);

        $this->assertEquals('gpt-3.5-turbo', $request->getModel());
        $this->assertEquals(0.5, $request->getTemperature());
        $this->assertEquals(500, $request->getMaxTokens());
        $this->assertTrue($request->isStream());
    }

    public function testFunctionsConfiguration()
    {
        $functions = [
            [
                'name' => 'get_weather',
                'description' => 'Get weather information',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'location' => ['type' => 'string'],
                    ],
                ],
            ],
        ];

        $request = new ChatCompletionRequest([], null);
        $request->withFunctions($functions)->withFunctionCall('auto');

        $this->assertEquals($functions, $request->getFunctions());
        $this->assertEquals('auto', $request->getFunctionCall());
    }
}