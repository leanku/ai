
在 Laravel 项目中，可以创建配置文件 config/ai.php：
```php
<?php

return [
    'default' => env('AI_DEFAULT_PROVIDER', 'openai'),
    
    'providers' => [
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'timeout' => env('AI_TIMEOUT', 30),
            'max_retries' => env('AI_MAX_RETRIES', 3),
        ],
        
        'deepseek' => [
            'api_key' => env('DEEPSEEK_API_KEY'),
        ],
        
        'ollama' => [
            'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
        ],
    ],
];
```

然后在服务提供者中注册：

```php
<?php

namespace App\Providers;

use Leanku\Ai\AIClient;
use Illuminate\Support\ServiceProvider;

class AIServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(AIClient::class, function ($app) {
            return new AIClient(config('ai'));
        });
    }
}
```