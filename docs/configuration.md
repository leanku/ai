## 配置说明

### 最小配置

```php
$config = [
    'default' => 'openai',
    'providers' => [
        'openai' => [
            'api_key' => 'your-api-key-here',
        ],
    ],
];
$client = new AIClient($config);
```

### 完整配置示例
```php
$config = [
    'default' => 'openai',
    
    'providers' => [
        'openai' => [
            'api_key' => 'sk-your-openai-api-key',
            'base_url' => 'https://api.openai.com/v1', // 可选
            'timeout' => 30, // 请求超时（秒）
            'max_retries' => 3, // 最大重试次数
            'default_model' => 'gpt-3.5-turbo', // 默认模型
        ],
        
        'deepseek' => [
            'api_key' => 'your-deepseek-api-key',
            'base_url' => 'https://api.deepseek.com/v1',
            'timeout' => 60,
        ],
        
        'ollama' => [
            'base_url' => 'http://localhost:11434',
            'default_model' => 'llama2',
            'timeout' => 120, // Ollama 可能需要更长时间
        ],
    ],
];
```

### 环境变量配置
推荐使用环境变量来管理敏感信息：
```php
$config = [
    'default' => 'openai',
    'providers' => [
        'openai' => [
            'api_key' => $_ENV['OPENAI_API_KEY'],
        ],
        'deepseek' => [
            'api_key' => $_ENV['DEEPSEEK_API_KEY'],
        ],
    ],
];
```