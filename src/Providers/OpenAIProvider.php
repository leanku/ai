<?php
declare(strict_types=1);

namespace Leanku\Ai\Providers;

use Leanku\Ai\Requests\ChatCompletionRequest;
use Leanku\Ai\Requests\EmbeddingRequest;
use Leanku\Ai\Contracts\ResponseInterface;
use Leanku\Ai\Responses\Adaptors\OpenAIAdaptor;
use Leanku\Ai\Exceptions\RequestException;

class OpenAIProvider extends AbstractProvider
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
            throw new RequestException('OpenAI API request failed: ' . $e->getMessage(), $e->getCode(), $e);
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
            throw new RequestException('OpenAI stream request failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function embeddings(EmbeddingRequest $request): ResponseInterface
    {
        // 实现嵌入向量功能
        throw new \RuntimeException('Embeddings not implemented for OpenAI yet');
    }

    public function getName(): string
    {
        return 'openai';
    }

    private function prepareChatPayload(ChatCompletionRequest $request): array
    {
        $payload = [
            'model'       => $request->getModel() ?: $this->config->get('default_model', 'gpt-3.5-turbo'),
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

        // 移除空值
        return array_filter($payload, function ($value) {
            return $value !== null && $value !== '';
        });
    }
}