<?php

namespace Leanku\Ai\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getConfig(string $provider = 'openai'): array
    {
        return [
            'default'   => $provider,
            'providers' => [
                'openai'   => [
                    'api_key'       => 'test-api-key',
                    'base_url'      => 'https://api.openai.com/v1',
                    'timeout'       => 30,
                    'max_retries'   => 3,
                    'default_model' => 'gpt-3.5-turbo',
                ],
                'deepseek' => [
                    'api_key'       => 'you key',
                    'base_url'      => 'https://api.deepseek.com/v1',
                    'timeout'       => 30,
                    'max_retries'   => 3,
                    'default_model' => 'deepseek-chat',
                ],
                'ollama'   => [
                    'base_url'      => 'http://localhost:11434',
                    'default_model' => 'gemma3:1b',
                    'timeout'       => 120, // Ollama 通常需要更长的超时时间
                    'max_retries'   => 2,
                ],
            ],
        ];
    }

    protected function getMockResponse(string $content = 'Hello, world!'): array
    {
        return [
            'id'      => 'chatcmpl-123',
            'object'  => 'chat.completion',
            'created' => 1677652288,
            'model'   => 'gpt-3.5-turbo-0613',
            'choices' => [
                [
                    'index'         => 0,
                    'message'       => [
                        'role'    => 'assistant',
                        'content' => $content,
                    ],
                    'finish_reason' => 'stop',
                ],
            ],
            'usage'   => [
                'prompt_tokens'     => 9,
                'completion_tokens' => 12,
                'total_tokens'      => 21,
            ],
        ];
    }

    protected function getMockStreamResponse(): array
    {
        return [
            'id'      => 'chatcmpl-123',
            'object'  => 'chat.completion.chunk',
            'created' => 1677652288,
            'model'   => 'gpt-3.5-turbo-0613',
            'choices' => [
                [
                    'index'         => 0,
                    'delta'         => [
                        'content' => 'Hello',
                    ],
                    'finish_reason' => null,
                ],
            ],
        ];
    }

    protected function getMockHttpClient($responseData = null, int $statusCode = 200): \GuzzleHttp\Client
    {
        if ($responseData === null) {
            $responseData = $this->getMockResponse();
        }

        $response = new \GuzzleHttp\Psr7\Response(
            $statusCode,
            ['Content-Type' => 'application/json'],
            json_encode($responseData)
        );

        $mock = new \GuzzleHttp\MockHandler([$response]);

        return new \GuzzleHttp\Client(['handler' => \GuzzleHttp\HandlerStack::create($mock)]);
    }

    protected function createStreamResponse(array $chunks, string $format = 'openai'): string
    {
        if ($format === 'ollama') {
            $lines = [];
            foreach ($chunks as $chunk) {
                $lines[] = 'data: ' . json_encode($chunk);
            }
            return implode("\n", $lines) . "\n\n";
        } else {
            // OpenAI 格式
            $lines = [];
            foreach ($chunks as $chunk) {
                $lines[] = 'data: ' . json_encode($chunk);
            }
            $lines[] = 'data: [DONE]';
            return implode("\n", $lines) . "\n\n";
        }
    }
}