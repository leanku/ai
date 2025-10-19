<?php
declare(strict_types=1);

namespace Leanku\Ai\Requests;

class ChatCompletionRequest
{
    public function __construct(
        private array   $messages,
        private ?string $model = null,
        private float   $temperature = 0.7,
        private int     $maxTokens = 1000,
        private array   $functions = [],
        private ?string $functionCall = null,
        private bool    $stream = false
    )
    {
    }

    // Getters
    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function getTemperature(): float
    {
        return $this->temperature;
    }

    public function getMaxTokens(): int
    {
        return $this->maxTokens;
    }

    public function getFunctions(): array
    {
        return $this->functions;
    }

    public function getFunctionCall(): ?string
    {
        return $this->functionCall;
    }

    public function isStream(): bool
    {
        return $this->stream;
    }

    // Fluent setters
    public function withModel(string $model): self
    {
        $this->model = $model;
        return $this;
    }

    public function withTemperature(float $temperature): self
    {
        $this->temperature = $temperature;
        return $this;
    }

    public function withMaxTokens(int $maxTokens): self
    {
        $this->maxTokens = $maxTokens;
        return $this;
    }

    public function withFunctions(array $functions): self
    {
        $this->functions = $functions;
        return $this;
    }

    public function withFunctionCall(?string $functionCall): self
    {
        $this->functionCall = $functionCall;
        return $this;
    }

    public function withStream(bool $stream = true): self
    {
        $this->stream = $stream;
        return $this;
    }
}