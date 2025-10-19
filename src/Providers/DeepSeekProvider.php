<?php
declare(strict_types=1);
/**
 * time flies.
 * Date : 2025/10/19
 * By : PhpStorm
 * Author : leanku
 */

namespace Leanku\Ai\Providers;

use Leanku\Ai\Exceptions\RequestException;
use Leanku\Ai\Requests\ChatCompletionRequest;
use Leanku\Ai\Responses\Adaptors\OpenAIAdaptor;
use Leanku\Ai\Contracts\ResponseInterface;
use Leanku\Ai\Requests\EmbeddingRequest;

class DeepSeekProvider extends AbstractProvider
{
    public function chatCompletions(ChatCompletionRequest $request): ResponseInterface
    {
        $payload = $this->prepareChatPayload($request);

        try {
            $response = $this->sendRequest('POST', '/chat/completions', [
                'json' => $payload,
            ]);

            return OpenAIAdaptor::adapt($response);
        } catch (\Exception $e) {
            throw new RequestException('DeepSeek API request failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function chatCompletionsStream(ChatCompletionRequest $request): \Generator
    {
        $payload = $this->prepareChatPayload($request->withStream(true));

        try {
            $response = $this->sendRequest('POST', '/chat/completions', [
                'json' => $payload,
            ]);

            return $this->handleStreamResponse($response, function ($chunk) {
                return OpenAIAdaptor::adaptStream($chunk);
            });
        } catch (\Exception $e) {
            throw new RequestException('DeepSeek stream request failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function embeddings(EmbeddingRequest $request): ResponseInterface
    {
        // DeepSeek 可能不支持嵌入向量，根据官方文档决定是否实现
        throw new \RuntimeException('Embeddings not implemented for DeepSeek yet');
    }

    public function getName(): string
    {
        return 'deepseek';
    }

    private function prepareChatPayload(ChatCompletionRequest $request): array
    {
        $payload = [
            'model'       => $request->getModel() ?: $this->config->get('default_model', 'deepseek-chat'),
            'messages'    => $request->getMessages(),
            'temperature' => $request->getTemperature(),
            'max_tokens'  => $request->getMaxTokens(),
            'stream'      => $request->isStream(),
        ];

        if (!empty($request->getFunctions())) {
            $payload['functions'] = $request->getFunctions();
            if ($request->getFunctionCall()) {
                $payload['function_call'] = $request->getFunctionCall();
            }
        }

        return array_filter($payload, function ($value) {
            return $value !== null && $value !== '';
        });
    }
}