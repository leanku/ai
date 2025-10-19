<?php
declare(strict_types=1);

namespace Leanku\Ai\Responses\Adaptors;

use Leanku\Ai\Responses\ApiResponse;
use Leanku\Ai\Contracts\ResponseInterface;
use Psr\Http\Message\ResponseInterface as HttpResponse;

class OpenAIAdaptor
{
    public static function adapt(HttpResponse $response): ResponseInterface
    {
        $data = json_decode($response->getBody()->getContents(), true);

        if (!isset($data['choices'][0]['message'])) {
            throw new \RuntimeException('Invalid OpenAI response format');
        }

        $message = $data['choices'][0]['message'];
        $choice = $data['choices'][0];

        return new ApiResponse(
            content: $message['content'] ?? '',
            role: $message['role'] ?? 'assistant',
            model: $data['model'] ?? 'unknown',
            usage: $data['usage'] ?? [],
            data: $data,
            successful: true,
            finishReason: $choice['finish_reason'] ?? null
        );
    }

    public static function adaptStream(array $chunk): ?ResponseInterface
    {
        if (empty($chunk['choices'][0]['delta'])) {
            return null;
        }

        $delta = $chunk['choices'][0]['delta'];

        return new ApiResponse(
            content: $delta['content'] ?? '',
            role: $delta['role'] ?? 'assistant',
            model: $chunk['model'] ?? 'unknown',
            usage: [],
            data: $chunk,
            successful: true,
            finishReason: $chunk['choices'][0]['finish_reason'] ?? null
        );
    }
}