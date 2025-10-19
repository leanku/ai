<?php
declare(strict_types=1);
/**
 * 提供流畅的 API 来构建复杂对话。
 */

namespace Leanku\Ai\Builders;

use Leanku\Ai\Clients\AIClient;
use Leanku\Ai\Contracts\ResponseInterface;

class MessageBuilder
{
    private AIClient $client;
    private array $messages = [];
    private ?string $model;
    private array $options = [];

    public function __construct(AIClient $client, string $message, ?string $model = null)
    {
        $this->client = $client;
        $this->model = $model;
        $this->messages[] = ['role' => 'user', 'content' => $message];
    }

    /**
     * Desc : 添加系统消息。
     * @param string $message
     * @return $this
     * @author: leanku@hotmail.com
     * date: 2025/10/19
     */
    public function fromSystem(string $message): self
    {
        array_unshift($this->messages, ['role' => 'system', 'content' => $message]);
        return $this;
    }

    // 添加助手消息。
    public function fromAssistant(string $message): self
    {
        $this->messages[] = ['role' => 'assistant', 'content' => $message];
        return $this;
    }

    // 添加用户消息。
    public function andThen(string $message): self
    {
        $this->messages[] = ['role' => 'user', 'content' => $message];
        return $this;
    }

    public function withOption(string $key, $value): self
    {
        $this->options[$key] = $value;
        return $this;
    }

    public function withTemperature(float $temperature): self
    {
        return $this->withOption('temperature', $temperature);
    }

    // 设置最大 token 数。
    public function withMaxTokens(int $maxTokens): self
    {
        return $this->withOption('maxTokens', $maxTokens);
    }

    // 执行请求并获取响应。
    public function get(): ResponseInterface
    {
        return $this->client->chat($this->messages, $this->model, $this->options);
    }

    // 执行流式请求。
    public function stream(): \Generator
    {
        return $this->client->chatStream($this->messages, $this->model, $this->options);
    }

}