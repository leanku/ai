```php
<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Leanku\Ai\AIClient;

$client = new AIClient([
    'default' => 'openai',
    'providers' => [
        'openai' => [
            'api_key' => getenv('OPENAI_API_KEY'),
        ],
    ],
]);

echo "=== 流式响应示例 ===\n";
echo "AI: ";

$stream = $client->ask('用流式方式介绍人工智能的历史')
    ->stream();

foreach ($stream as $chunk) {
    echo $chunk->getContent();
    ob_flush();
    flush();
    usleep(100000); // 100ms 延迟，模拟打字效果
}

echo "\n\n";
```