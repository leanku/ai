<?php
declare(strict_types=1);

namespace Leanku\Ai\Providers;

use Leanku\Ai\Contracts\ProviderInterface;
use Leanku\Ai\Support\Config;
use Leanku\Ai\Support\HttpClient;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface as HttpResponse;

abstract class AbstractProvider implements ProviderInterface
{
    protected Client $httpClient;
    protected Config $config;

    public function __construct(array $config = [])
    {
        $this->config = new Config($config);
        $this->httpClient = HttpClient::createClient([
            'base_uri'    => $this->config->get('base_url'),
            'timeout'     => $this->config->get('timeout', 30),
            'max_retries' => $this->config->get('max_retries', 3),
        ]);
    }

    protected function sendRequest(string $method, string $endpoint, array $options = []): HttpResponse
    {
        $headers = array_merge([
            'Content-Type' => 'application/json',
            'User-Agent'   => 'LeankuAI/1.0',
        ], $options['headers'] ?? []);

        // 添加认证头
        if ($apiKey = $this->config->get('api_key')) {
            $headers['Authorization'] = 'Bearer ' . $apiKey;
        }

        $options['headers'] = $headers;

        return $this->httpClient->request($method, $endpoint, $options);
    }

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

                    if (trim($data) === '[DONE]') {
                        break 2; // 跳出内外两层循环
                    }

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
    }
}