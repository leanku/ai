<?php

namespace Leanku\Ai\Tests\Providers;

use Leanku\Ai\Providers\DeepSeekProvider;
use Leanku\Ai\Requests\ChatCompletionRequest;
use Leanku\Ai\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use ReflectionClass;

class DeepSeekProviderTest extends TestCase
{
    private function createProviderWithMockClient(MockHandler $mockHandler, array $config = []): DeepSeekProvider
    {
        $config = array_merge($this->getConfig()['providers']['deepseek'], $config);

        $provider = new DeepSeekProvider($config);

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
            'id' => 'deepseek-chat-123',
            'object' => 'chat.completion',
            'created' => 1677652288,
            'model' => 'deepseek-chat',
            'choices' => [
                [
                    'index' => 0,
                    'message' => [
                        'role' => 'assistant',
                        'content' => '我是DeepSeek，很高兴为你服务！',
                    ],
                    'finish_reason' => 'stop',
                ],
            ],
            'usage' => [
                'prompt_tokens' => 10,
                'completion_tokens' => 15,
                'total_tokens' => 25,
            ],
        ];

        $mockHandler = new MockHandler([
            new Response(200, [], json_encode($mockResponse)),
        ]);

        $provider = $this->createProviderWithMockClient($mockHandler);

        $request = new ChatCompletionRequest([
            ['role' => 'user', 'content' => '请介绍一下你自己'],
        ], 'deepseek-chat');

        $response = $provider->chatCompletions($request);
//        var_dump($response);

        $this->assertEquals('我是DeepSeek，很高兴为你服务！', $response->getContent());
        $this->assertEquals('assistant', $response->getRole());
        $this->assertEquals('deepseek-chat', $response->getModel());
        $this->assertTrue($response->isSuccessful());

        $lastRequest = $mockHandler->getLastRequest();
        $this->assertEquals('POST', $lastRequest->getMethod());
        $this->assertEquals('/chat/completions', $lastRequest->getUri()->getPath());

        $requestBody = json_decode($lastRequest->getBody()->getContents(), true);
        $this->assertEquals('deepseek-chat', $requestBody['model']);
    }

    public function testGetName()
    {
        $provider = new DeepSeekProvider([]);
        $this->assertEquals('deepseek', $provider->getName());
    }

    // 可以添加更多测试方法，例如测试流式响应等

    /*
 object(Leanku\Ai\Responses\ApiResponse)#103 (7) {
  ["content":"Leanku\Ai\Responses\ApiResponse":private]=>
  string(41) "我是DeepSeek，很高兴为你服务！"
  ["role":"Leanku\Ai\Responses\ApiResponse":private]=>
  string(9) "assistant"
  ["model":"Leanku\Ai\Responses\ApiResponse":private]=>
  string(13) "deepseek-chat"
  ["usage":"Leanku\Ai\Responses\ApiResponse":private]=>
  array(3) {
    ["prompt_tokens"]=>
    int(10)
    ["completion_tokens"]=>
    int(15)
    ["total_tokens"]=>
    int(25)
  }
  ["data":"Leanku\Ai\Responses\ApiResponse":private]=>
  array(6) {
    ["id"]=>
    string(17) "deepseek-chat-123"
    ["object"]=>
    string(15) "chat.completion"
    ["created"]=>
    int(1677652288)
    ["model"]=>
    string(13) "deepseek-chat"
    ["choices"]=>
    array(1) {
      [0]=>
      array(3) {
        ["index"]=>
        int(0)
        ["message"]=>
        array(2) {
          ["role"]=>
          string(9) "assistant"
          ["content"]=>
          string(41) "我是DeepSeek，很高兴为你服务！"
        }
        ["finish_reason"]=>
        string(4) "stop"
      }
    }
    ["usage"]=>
    array(3) {
      ["prompt_tokens"]=>
      int(10)
      ["completion_tokens"]=>
      int(15)
      ["total_tokens"]=>
      int(25)
    }
  }
  ["successful":"Leanku\Ai\Responses\ApiResponse":private]=>
  bool(true)
  ["finishReason":"Leanku\Ai\Responses\ApiResponse":private]=>
  string(4) "stop"
}
     */
}