<?php
declare(strict_types=1);

namespace Leanku\Ai\Responses;

use Leanku\Ai\Contracts\ResponseInterface;

class ApiResponse implements ResponseInterface
{
    public function __construct(
        private string  $content,
        private string  $role,
        private string  $model,
        private array   $usage,
        private array   $data,
        private bool    $successful = true,
        private ?string $finishReason = null
    )
    {
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getUsage(): array
    {
        return $this->usage;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function isSuccessful(): bool
    {
        return $this->successful;
    }

    public function getFinishReason(): ?string
    {
        return $this->finishReason;
    }
}