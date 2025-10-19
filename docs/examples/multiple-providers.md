
```php

<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Leanku\Ai\AIClient;

$config = [
    'default' => 'openai',
    'providers' => [
        'openai' => [
            'api_key' => getenv('OPENAI_API_KEY'),
        ],
        'ollama' => [
            'base_url' => 'http://localhost:11434',
        ],
    ],
];

$client = new AIClient($config);

// 使用 OpenAI
echo "=== OpenAI 响应 ===\n";
$response = $client->chat([
    ['role' => 'user', 'content' => 'What is machine learning?']
]);
echo $response->getContent() . "\n\n";

// 切换到 Ollama
echo "=== Ollama 响应 ===\n";
$ollamaClient = $client->withProvider('ollama');
$response = $ollamaClient->chat([
    ['role' => 'user', 'content' => 'What is machine learning?']
], 'llama2');
echo $response->getContent() . "\n";
```