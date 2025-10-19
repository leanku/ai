<?php
/**
 * time flies.
 * Date : 2025/10/19
 * By : PhpStorm
 * Author : leanku
 */

namespace Leanku\Ai\Providers;

use Leanku\Ai\Contracts\ResponseInterface;
use Leanku\Ai\Exceptions\RequestException;
use Leanku\Ai\Requests\ChatCompletionRequest;
use Leanku\Ai\Requests\EmbeddingRequest;
use Leanku\Ai\Responses\ApiResponse;
use Psr\Http\Message\ResponseInterface as HttpResponse;

class OllamaProvider extends AbstractProvider
{

    public function chatCompletions(ChatCompletionRequest $request): ResponseInterface
    {
        $payload = $this->prepareChatPayload($request);
        try {
            $response = $this->sendRequest('POST', '/api/chat', [
                'json' => $payload,
            ]);

            return $this->adaptResponse($response);
        } catch (\Exception $e) {
            throw new RequestException('Ollama API request failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function chatCompletionsStream(ChatCompletionRequest $request): \Generator
    {
        $payload = $this->prepareChatPayload($request->withStream(true));

        try {
            $response = $this->sendRequest('POST', '/api/chat', [
                'json' => $payload,
            ]);

            return $this->handleStreamResponse($response, function ($chunk) {
                return $this->adaptStreamResponse($chunk);
            });
        } catch (\Exception $e) {
            throw new RequestException('Ollama stream request failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function embeddings(EmbeddingRequest $request): ResponseInterface
    {
        $payload = [
            'model'  => $request->getModel() ?: $this->config->get('default_model', 'llama2'),
            'prompt' => implode("\n", $request->getInput()),
        ];

        try {
            $response = $this->sendRequest('POST', '/api/embeddings', [
                'json' => $payload,
            ]);

            return $this->adaptEmbeddingResponse($response);
        } catch (\Exception $e) {
            throw new RequestException('Ollama embeddings request failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function listModels()
    {
        try {
            $response = $this->sendRequest('GET', '/api/tags');
            $data = json_decode($response->getBody()->getContents(), true);

            return $data['models'] ?? [];
        } catch (\Exception $e) {
            throw new RequestException('Ollama list models failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getName(): string
    {
        return 'ollama';
    }

    private function prepareChatPayload(ChatCompletionRequest $request): array
    {
        $messages = $request->getMessages();

        // Ollama 使用不同的消息格式
        $ollamaMessages = array_map(function ($message) {
            return [
                'role'    => $message['role'],
                'content' => $message['content'],
            ];
        }, $messages);

        $payload = [
            'model'    => $request->getModel() ?: $this->config->get('default_model', 'llama2'),
            'messages' => $ollamaMessages,
            'stream'   => $request->isStream(),
        ];

        // Ollama 特有的参数
        if ($request->getTemperature() !== 0.7) {
            $payload['options']['temperature'] = $request->getTemperature();
        }

        if ($request->getMaxTokens() !== 1000) {
            $payload['options']['num_predict'] = $request->getMaxTokens();
        }

        return array_filter($payload, function ($value) {
            return $value !== null && $value !== '';
        });
    }

    private function adaptResponse(HttpResponse $response): ResponseInterface
    {
        $data = json_decode($response->getBody()->getContents(), true);

        if (!isset($data['message'])) {
            throw new \RuntimeException('Invalid Ollama response format');
        }

        $message = $data['message'];

        return new ApiResponse(
            content: $message['content'] ?? '',
            role: $message['role'] ?? 'assistant',
            model: $data['model'] ?? 'unknown',
            usage: [
                'prompt_tokens'     => $data['prompt_eval_count'] ?? 0,
                'completion_tokens' => $data['eval_count'] ?? 0,
                'total_tokens'      => ($data['prompt_eval_count'] ?? 0) + ($data['eval_count'] ?? 0),
            ],
            data: $data,
            successful: true,
            finishReason: $data['done_reason'] ?? null
        );
    }

    private function adaptStreamResponse(array $chunk): ?ResponseInterface
    {
        if (!isset($chunk['message'])) {
            return null;
        }

        $message = $chunk['message'];

        return new ApiResponse(
            content: $message['content'] ?? '',
            role: $message['role'] ?? 'assistant',
            model: $chunk['model'] ?? 'unknown',
            usage: [
                'prompt_tokens'     => $chunk['prompt_eval_count'] ?? 0,
                'completion_tokens' => $chunk['eval_count'] ?? 0,
                'total_tokens'      => ($chunk['prompt_eval_count'] ?? 0) + ($chunk['eval_count'] ?? 0),
            ],
            data: $chunk,
            successful: true,
            finishReason: $chunk['done'] ? ($chunk['done_reason'] ?? 'stop') : null
        );
    }

    private function adaptEmbeddingResponse(HttpResponse $response): ResponseInterface
    {
        $data = json_decode($response->getBody()->getContents(), true);

        if (!isset($data['embedding'])) {
            throw new \RuntimeException('Invalid Ollama embeddings response format');
        }

        return new ApiResponse(
            content: '',
            role: 'system',
            model: $data['model'] ?? 'unknown',
            usage: [],
            data: [
                'embedding' => $data['embedding'],
                'model'     => $data['model'],
            ],
            successful: true,
            finishReason: null
        );
    }

    /**
     * 重写流式响应处理，Ollama 的流式格式不同
     */
    protected function handleStreamResponse(HttpResponse $response, callable $adaptor): \Generator
    {
        $body = $response->getBody();
        $buffer = '';

        while (!$body->eof()) {
            $chunk = $body->read(1024); // 读取 1KB 数据块
            $buffer .= $chunk;
            // 按行分割处理
            $lines = explode("\n", $buffer);
            // 保留最后一行不完整的部分
            $buffer = array_pop($lines);

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) {
                    continue;
                }

                if (strpos($line, 'data: ') === 0) {
                    $data = substr($line, 6);

                    if (trim($data) === '') {
                        continue;
                    }

                    $decoded = json_decode($data, true);
                    if ($decoded && json_last_error() === JSON_ERROR_NONE) {
                        $adapted = $adaptor($decoded);
                        if ($adapted) {
                            yield $adapted;
                        }

                        // 如果流结束，提前退出
                        if (isset($decoded['done']) && $decoded['done'] === true) {
                            break 2; // 跳出内外两层循环
                        }
                    }
                }
            }
        }

        // 处理缓冲区中剩余的数据
        if (!empty(trim($buffer)) && strpos($buffer, 'data: ') === 0) {
            $data = substr($buffer, 6);
            $decoded = json_decode($data, true);
            if ($decoded && json_last_error() === JSON_ERROR_NONE) {
                $adapted = $adaptor($decoded);
                if ($adapted) {
                    yield $adapted;
                }
            }
        }
    }
}