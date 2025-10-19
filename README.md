# leanku/ai

🚀 一个简单、可扩展的 AI SDK，支持多种 AI 提供商（OpenAI, DeepSeek, Ollama 等）。

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-8892BF.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
## 特性
-  **多提供商支持** - OpenAI, DeepSeek, Ollama 等
-  **统一接口** - 所有提供商使用相同的 API
-  **可扩展** - 轻松添加新的 AI 提供商

## 安装
```bash
composer require leanku/ai
```

## 快速开始
### 基础使用
```php
use Leanku\Ai\AIClient;

$client = new AIClient([
    'default' => 'openai',
    'providers' => [
        'openai' => [
            'api_key' => 'your-openai-api-key',
        ],
    ],
]);

$response = $client->chat([
    ['role' => 'user', 'content' => 'Hello, how are you?']
]);

echo $response->getContent(); // "Hello! I'm doing well, thank you for asking!"
```

### 使用建造者模式（推荐）
```php
$response = $client->ask('What is PHP?')
    ->fromSystem('You are a helpful programming assistant.')
    ->andThen('Can you give me an example?')
    ->get();

echo $response->getContent();
```

### 流式响应
```php
$stream = $client->chatStream([
    ['role' => 'user', 'content' => 'Tell me a long story...']
]);

foreach ($stream as $chunk) {
    echo $chunk->getContent();
    ob_flush();
    flush();
}
```

## 文档

-   [完整使用指南](docs/usage.md)

-   [配置说明](docs/configuration.md)

-   [示例代码](docs/examples/)

## 贡献


## 许可证

本项目基于 MIT 许可证开源。详见 [LICENSE](LICENSE) 文件。


