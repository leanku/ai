<?php
declare(strict_types=1);
/**
 * 主客户端类，提供统一的 AI 服务接口。
 */

namespace Leanku\Ai\Clients;

use Leanku\Ai\Contracts\ResponseInterface;
use Leanku\Ai\Providers\ProviderFactory;
use Leanku\Ai\Contracts\ProviderInterface;
use Leanku\Ai\Requests\ChatCompletionRequest;
use Leanku\Ai\Requests\EmbeddingRequest;
use Leanku\Ai\Builders\MessageBuilder;

class AIClient
{
    private ProviderInterface $provider;

    /**
     * @param array $config 配置数组
     */
    public function __construct(array $config = [])
    {
        $defaultProvider = $config['default'] ?? 'openai';
        $providerConfig = $config['providers'][$defaultProvider] ?? [];

        $this->provider = ProviderFactory::create($defaultProvider, $providerConfig);
    }

    /**
     * Description : 发送聊天消息。
     * @param array       $messages 消息数组
     * @param string|null $model    模型名称（可选）
     * @param array       $options  额外选项
     * @return ResponseInterface
     * @author: leanku leanku@hotmail.com
     *                              date: 2025/10/19
     */
    public function chat(array $messages, ?string $model = null, array $options = []): ResponseInterface
    {
        $request = new ChatCompletionRequest($messages, $model);

        // 应用选项
        foreach ($options as $key => $value) {
            $method = 'with' . ucfirst($key);
            if (method_exists($request, $method)) {
                $request = $request->$method($value);
            }
        }

        return $this->provider->chatCompletions($request);
    }

    /**
     * Description : 创建消息建造者。
     * @param string      $message 用户消息
     * @param string|null $model   模型名称（可选）
     * @return MessageBuilder
     * @author: leanku@hotmail.com
     *                             date: 2025/10/19
     */
    public function ask(string $message, ?string $model = null): MessageBuilder
    {
        return new MessageBuilder($this, $message, $model);
    }

    /**
     * Desc : 发送流式聊天消息。
     * @param array       $messages 消息数组
     * @param string|null $model    模型名称（可选）
     * @param array       $options  额外选项
     * @return \Generator
     * @author: leanku@hotmail.com
     *                              date: 2025/10/19
     */
    public function chatStream(array $messages, ?string $model = null, array $options = []): \Generator
    {
        $request = new ChatCompletionRequest($messages, $model);
        $request = $request->withStream(true);

        foreach ($options as $key => $value) {
            $method = 'with' . ucfirst($key);
            if (method_exists($request, $method)) {
                $request = $request->$method($value);
            }
        }

        return $this->provider->chatCompletionsStream($request);
    }

    public function embeddings(array $input, ?string $model = null)
    {
        $request = new EmbeddingRequest($input, $model);
        return $this->provider->embeddings($request);
    }

    /**
     * Desc :切换提供商。
     * @param string $provider 提供商名称
     * @param array  $config   提供商配置
     * @return self
     * @author: leanku@hotmail.com
     *                         date: 2025/10/19
     */
    public function withProvider(string $provider, array $config = []): self
    {
        $newConfig = ['default' => $provider];
        if (!empty($config)) {
            $newConfig['providers'] = [$provider => $config];
        }

        return new self($newConfig);
    }

    public function getProvider(): ProviderInterface
    {
        return $this->provider;
    }

}