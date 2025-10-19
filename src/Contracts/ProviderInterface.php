<?php
declare(strict_types=1);

namespace Leanku\Ai\Contracts;

use Leanku\Ai\Requests\ChatCompletionRequest;
use Leanku\Ai\Requests\EmbeddingRequest;

interface ProviderInterface
{
    public function chatCompletions(ChatCompletionRequest $request): ResponseInterface;

    public function chatCompletionsStream(ChatCompletionRequest $request): \Generator;

    public function embeddings(EmbeddingRequest $request): ResponseInterface;

    public function getName(): string;
}