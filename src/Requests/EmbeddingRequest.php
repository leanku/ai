<?php
declare(strict_types=1);

namespace Leanku\Ai\Requests;

class EmbeddingRequest
{

    public function __construct(
        private array   $input,
        private ?string $model = null
    )
    {
    }

    public function getInput(): array
    {
        return $this->input;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function withModel(string $model): self
    {
        $this->model = $model;
        return $this;
    }

}