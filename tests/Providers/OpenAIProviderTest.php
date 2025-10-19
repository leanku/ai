<?php

namespace Leanku\Ai\Tests\Providers;

use Leanku\Ai\Providers\OpenAIProvider;
use Leanku\Ai\Requests\ChatCompletionRequest;
use Leanku\Ai\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use ReflectionClass;

class OpenAIProviderTest extends TestCase
{
    private function createProviderWithMockClient(MockHandler $mockHandler, array $config = []): OpenAIProvider
    {
        $config = array_merge($this->getConfig()['providers']['openai'], $config);

        $provider = new OpenAIProvider($config);

        // 使用反射注入 mock client
        $reflection = new ReflectionClass($provider);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);

        $client = new Client(['handler' => \GuzzleHttp\HandlerStack::create($mockHandler)]);
        $property->setValue($provider, $client);

        return $provider;
    }

    public function testChatCompletions()
    {
        $mockResponse = $this->getMockResponse('Mocked AI response');
        $mockHandler = new MockHandler([
            new Response(200, [], json_encode($mockResponse)),
        ]);

        $provider = $this->createProviderWithMockClient($mockHandler);

        $request = new ChatCompletionRequest([
            ['role' => 'user', 'content' => 'Hello'],
        ], 'gpt-3.5-turbo');

        $response = $provider->chatCompletions($request);

        $this->assertEquals('Mocked AI response', $response->getContent());
        $this->assertEquals('assistant', $response->getRole());
        $this->assertTrue($response->isSuccessful());

        // 验证请求
        $lastRequest = $mockHandler->getLastRequest();
        $this->assertEquals('POST', $lastRequest->getMethod());
        $this->assertEquals('/chat/completions', $lastRequest->getUri()->getPath());

        $requestBody = json_decode($lastRequest->getBody()->getContents(), true);
        $this->assertEquals('gpt-3.5-turbo', $requestBody['model']);
        $this->assertEquals([['role' => 'user', 'content' => 'Hello']], $requestBody['messages']);
    }

    public function testChatCompletionsWithFunctions()
    {
        $mockHandler = new MockHandler([
            new Response(200, [], json_encode($this->getMockResponse())),
        ]);

        $provider = $this->createProviderWithMockClient($mockHandler);

        $functions = [
            [
                'name' => 'test_function',
                'description' => 'A test function',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'param' => ['type' => 'string'],
                    ],
                ],
            ],
        ];

        $request = new ChatCompletionRequest([], 'gpt-3.5-turbo');
        $request->withFunctions($functions)->withFunctionCall('auto');

        $provider->chatCompletions($request);

        $lastRequest = $mockHandler->getLastRequest();
        $requestBody = json_decode($lastRequest->getBody()->getContents(), true);

        $this->assertEquals($functions, $requestBody['functions']);
        $this->assertEquals('auto', $requestBody['function_call']);
    }

    public function testChatCompletionsStream()
    {
        $streamData = [
            "data: " . json_encode($this->getMockStreamResponse()) . "\n\n",
            "data: [DONE]\n\n",
        ];

        $mockHandler = new MockHandler([
            new Response(200, ['Content-Type' => 'text/plain'], implode('', $streamData)),
        ]);

        $provider = $this->createProviderWithMockClient($mockHandler);

        $request = new ChatCompletionRequest([], 'gpt-3.5-turbo');
        $request->withStream(true);

        $generator = $provider->chatCompletionsStream($request);
        $chunks = iterator_to_array($generator);

        $this->assertCount(1, $chunks);
        $this->assertEquals('Hello', $chunks[0]->getContent());
    }

    public function testGetName()
    {
        $provider = new OpenAIProvider([]);
        $this->assertEquals('openai', $provider->getName());
    }
}