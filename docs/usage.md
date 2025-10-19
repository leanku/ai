
# 使用指南

## 基础用法

### 1. 初始化客户端

```php
use Leanku\Ai\AIClient;

$client = new AIClient([
    'default' => 'openai',
    'providers' => [
        'openai' => [
            'api_key' => 'your-api-key',
        ],
    ],
]);
```
### 2. 发送聊天消息
```php
$response = $client->chat([
    ['role' => 'user', 'content' => 'Hello, AI!']
]);

echo $response->getContent();
```

### 3. 多轮对话
```php
$messages = [
    ['role' => 'system', 'content' => 'You are a helpful assistant.'],
    ['role' => 'user', 'content' => 'What is the capital of France?'],
    ['role' => 'assistant', 'content' => 'The capital of France is Paris.'],
    ['role' => 'user', 'content' => 'What is its population?'],
];

$response = $client->chat($messages);
echo $response->getContent();
```


## 建造者模式
建造者模式提供了更流畅的 API：

### 基础建造者用法
```php
$response = $client->ask('What is PHP?')
    ->fromSystem('You are a programming expert.')
    ->get();

echo $response->getContent();
```
### 多轮对话建造者
```php
$response = $client->ask('Tell me about Python')
    ->fromSystem('You are a programming tutor.')
    ->fromAssistant('Python is a high-level programming language...')
    ->andThen('How does it compare to PHP?')
    ->get();
```

## 配置选项
```php
$response = $client->ask('Write a creative story')
    ->withTemperature(0.9)  // 更创造性
    ->withMaxTokens(1000)   // 更长响应
    ->get();
```

## 流式响应
对于长文本生成，可以使用流式响应：

### 基础流式响应
```php
$stream = $client->chatStream([
    ['role' => 'user', 'content' => 'Write a long article about AI...']
]);

foreach ($stream as $chunk) {
    echo $chunk->getContent();
    ob_flush();
    flush();
}
```

### 建造者模式的流式响应
```php
$stream = $client->ask('Explain machine learning in detail')
    ->withTemperature(0.7)
    ->stream();

foreach ($stream as $chunk) {
    echo $chunk->getContent();
    ob_flush();
    flush();
}
```

## 错误处理
### 基础错误处理
```php
use Leanku\Ai\Exceptions\RequestException;

try {
    $response = $client->chat([
        ['role' => 'user', 'content' => 'Hello']
    ]);
    
    if ($response->isSuccessful()) {
        echo $response->getContent();
    } else {
        echo "Request failed";
    }
    
} catch (RequestException $e) {
    echo "API Error: " . $e->getMessage();
} catch (\Exception $e) {
    echo "Unexpected error: " . $e->getMessage();
}
```

### 检查响应状态
```php
$response = $client->chat([...]);

if ($response->isSuccessful()) {
    echo "Content: " . $response->getContent();
    echo "Model: " . $response->getModel();
    echo "Tokens used: " . json_encode($response->getUsage());
    echo "Finish reason: " . $response->getFinishReason();
} else {
    echo "Request failed";
}
```

## 自定义提供商
### 1.创建新的提供商
```php
<?php

namespace App\AI\Providers;

use Leanku\Ai\Providers\AbstractProvider;
use Leanku\Ai\Requests\ChatCompletionRequest;
use Leanku\Ai\Contracts\ResponseInterface;

class CustomAIProvider extends AbstractProvider
{
    public function chatCompletions(ChatCompletionRequest $request): ResponseInterface
    {
        // 实现自定义逻辑
        $payload = $this->preparePayload($request);
        
        $response = $this->sendRequest('POST', '/chat', [
            'json' => $payload,
        ]);
        
        return $this->adaptResponse($response);
    }
    
    public function getName(): string
    {
        return 'custom-ai';
    }
    
    // ... 其他必要方法
}
```
### 2. 注册自定义提供商
```php
use Leanku\Ai\Providers\ProviderFactory;

ProviderFactory::register('custom-ai', CustomAIProvider::class);

$client = new AIClient([
    'default' => 'custom-ai',
    'providers' => [
        'custom-ai' => [
            'api_key' => 'your-custom-key',
            'base_url' => 'https://api.custom-ai.com/v1',
        ],
    ],
]);
```

## 响应处理
### 访问原始数据
```php
$response = $client->chat([...]);

// 获取统一格式的内容
echo $response->getContent();

// 获取原始 API 响应数据
$rawData = $response->getData();
echo $rawData['id']; // chatcmpl-123
echo $rawData['created']; // 1677652288
```
### 使用情况统计
```php
$usage = $response->getUsage();

echo "Prompt tokens: " . ($usage['prompt_tokens'] ?? 0);
echo "Completion tokens: " . ($usage['completion_tokens'] ?? 0);
echo "Total tokens: " . ($usage['total_tokens'] ?? 0);
```

## 批量处理
### 并行处理多个请求
```php
use GuzzleHttp\Promise;

$promises = [
    'response1' => $client->chatAsync([...]),
    'response2' => $client->chatAsync([...]),
    'response3' => $client->chatAsync([...]),
];

$results = Promise\unwrap($promises);

foreach ($results as $key => $response) {
    echo "$key: " . $response->getContent() . "\n";
}
```