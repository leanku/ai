
```php
<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Leanku\Ai\AIClient;

// 基础配置
$config = [
    'default' => 'openai',
    'providers' => [
        'openai' => [
            'api_key' => getenv('OPENAI_API_KEY'),
        ],
    ],
];

$client = new AIClient($config);

// 基础聊天
echo "=== 基础聊天示例 ===\n";
$response = $client->chat([
    ['role' => 'user', 'content' => '用中文简单介绍一下你自己']
]);
echo "响应: " . $response->getContent() . "\n\n";

// 建造者模式
echo "=== 建造者模式示例 ===\n";
$response = $client->ask('PHP 是什么？')
    ->fromSystem('你是一个编程专家，用中文回答')
    ->get();
echo "响应: " . $response->getContent() . "\n\n";

// 带参数的请求
echo "=== 带参数请求示例 ===\n";
$response = $client->ask('写一个简短的故事')
    ->withTemperature(0.9)
    ->withMaxTokens(200)
    ->get();
echo "响应: " . $response->getContent() . "\n";
```