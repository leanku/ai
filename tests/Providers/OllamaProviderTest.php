<?php

namespace Leanku\Ai\Tests\Providers;

use Leanku\Ai\Providers\OllamaProvider;
use Leanku\Ai\Requests\ChatCompletionRequest;
use Leanku\Ai\Requests\EmbeddingRequest;
use Leanku\Ai\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use ReflectionClass;

class OllamaProviderTest extends TestCase
{
    private function createProviderWithMockClient(MockHandler $mockHandler, array $config = []): OllamaProvider
    {
        $config = array_merge($this->getConfig()['providers']['ollama'], $config);

        $provider = new OllamaProvider($config);

        $reflection = new ReflectionClass($provider);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);

        $client = new Client(['handler' => \GuzzleHttp\HandlerStack::create($mockHandler)]);
        $property->setValue($provider, $client);

        return $provider;
    }

    public function testChatCompletions()
    {
        $mockResponse = [
            'model' => 'llama2',
            'created_at' => '2023-08-04T08:52:19.385406455-07:00',
            'message' => [
                'role' => 'assistant',
                'content' => 'Hello! How can I help you today?',
            ],
            'done' => true,
            'done_reason' => 'stop',
            'prompt_eval_count' => 25,
            'eval_count' => 20,
        ];

        $mockHandler = new MockHandler([
            new Response(200, [], json_encode($mockResponse)),
        ]);

        $provider = $this->createProviderWithMockClient($mockHandler);

        $request = new ChatCompletionRequest([
            ['role' => 'user', 'content' => 'Hello'],
        ], 'llama2');

        $response = $provider->chatCompletions($request);

        $this->assertInstanceOf(\Leanku\Ai\Contracts\ResponseInterface::class, $response);
        $this->assertEquals('Hello! How can I help you today?', $response->getContent());
        $this->assertEquals('assistant', $response->getRole());
        $this->assertEquals('llama2', $response->getModel());

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals([
            'prompt_tokens' => 25,
            'completion_tokens' => 20,
            'total_tokens' => 45,
        ], $response->getUsage());

        // 查看请求内容
        $lastRequest = $mockHandler->getLastRequest();
        $this->assertEquals('POST', $lastRequest->getMethod());
        $this->assertEquals('/api/chat', $lastRequest->getUri()->getPath());

        $requestBody = json_decode($lastRequest->getBody()->getContents(), true);
        $this->assertEquals('llama2', $requestBody['model']);
        $this->assertEquals([['role' => 'user', 'content' => 'Hello']], $requestBody['messages']);
    }

    public function testChatCompletionsStream()
    {
        // 使用更简单的流式数据格式
        $streamData = implode("\n", [
            'data: {"model":"llama2","message":{"role":"assistant","content":"Hello"},"done":false}',
            'data: {"model":"llama2","message":{"role":"assistant","content":" there"},"done":false}',
            'data: {"model":"llama2","message":{"role":"assistant","content":"!"},"done":true,"done_reason":"stop"}',
            ''
        ]);

        $mockHandler = new MockHandler([
            new Response(200, ['Content-Type' => 'text/plain'], $streamData),
        ]);

        $provider = $this->createProviderWithMockClient($mockHandler);

        $request = new ChatCompletionRequest([], 'llama2');
        $request->withStream(true);

        $generator = $provider->chatCompletionsStream($request);
        $chunks = iterator_to_array($generator);

        $this->assertCount(3, $chunks);
        $this->assertEquals('Hello', $chunks[0]->getContent());
        $this->assertEquals(' there', $chunks[1]->getContent());
        $this->assertEquals('!', $chunks[2]->getContent());
        $this->assertEquals('stop', $chunks[2]->getFinishReason());
    }

    public function testEmbeddings()
    {
        $mockResponse = [
            'model' => 'llama2',
            'embedding' => [0.1, 0.2, 0.3, 0.4, 0.5],
        ];

        $mockHandler = new MockHandler([
            new Response(200, [], json_encode($mockResponse)),
        ]);

        $provider = $this->createProviderWithMockClient($mockHandler);

        $request = new EmbeddingRequest(['Hello world'], 'llama2');
        $response = $provider->embeddings($request);

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('llama2', $response->getModel());
        $this->assertEquals([
            'embedding' => [0.1, 0.2, 0.3, 0.4, 0.5],
            'model' => 'llama2',
        ], $response->getData());

        $lastRequest = $mockHandler->getLastRequest();
        $this->assertEquals('POST', $lastRequest->getMethod());
        $this->assertEquals('/api/embeddings', $lastRequest->getUri()->getPath());
    }

    public function testListModels()
    {
        $mockResponse = [
            'models' => [
                [
                    'name' => 'llama2:latest',
                    'modified_at' => '2023-08-04T08:52:19.385406455-07:00',
                    'size' => 3825819512,
                    'digest' => 'sha256:xxxx',
                ],
                [
                    'name' => 'codellama:latest',
                    'modified_at' => '2023-08-05T08:52:19.385406455-07:00',
                    'size' => 3825819513,
                    'digest' => 'sha256:yyyy',
                ],
            ],
        ];

        $mockHandler = new MockHandler([
            new Response(200, [], json_encode($mockResponse)),
        ]);

        $provider = $this->createProviderWithMockClient($mockHandler);
        $models = $provider->listModels();

        $this->assertCount(2, $models);
        $this->assertEquals('llama2:latest', $models[0]['name']);
        $this->assertEquals('codellama:latest', $models[1]['name']);

        $lastRequest = $mockHandler->getLastRequest();
        $this->assertEquals('GET', $lastRequest->getMethod());
        $this->assertEquals('/api/tags', $lastRequest->getUri()->getPath());
    }

    // 添加一个测试边缘情况的测试方法
    public function testChatCompletionsStreamWithIncompleteData()
    {
        // 模拟不完整的数据流
        $streamData = implode("\n", [
            'data: {"model":"llama2","message":{"role":"assistant","content":"Partial"},"done":false}',
            'incomplete line without data prefix', // 无效行
            '', // 空行
            'data: {"model":"llama2","message":{"role":"assistant","content":" data"},"done":true}',
            ''
        ]);

        $mockHandler = new MockHandler([
            new Response(200, ['Content-Type' => 'text/plain'], $streamData),
        ]);

        $provider = $this->createProviderWithMockClient($mockHandler);

        $request = new ChatCompletionRequest([], 'llama2');
        $request->withStream(true);

        $generator = $provider->chatCompletionsStream($request);
        $chunks = iterator_to_array($generator);

        // 应该只处理有效的 data: 行
        $this->assertCount(2, $chunks);
        $this->assertEquals('Partial', $chunks[0]->getContent());
        $this->assertEquals(' data', $chunks[1]->getContent());
    }


    public function testGetName()
    {
        $provider = new OllamaProvider([]);
        $this->assertEquals('ollama', $provider->getName());
    }
}