<?php
declare(strict_types=1);

namespace Leanku\Ai\Support;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpClient
{
    public static function createClient(array $config): Client
    {
        $stack = HandlerStack::create();

        $stack->push(Middleware::retry(
            function (
                int                $retries, RequestInterface $request,
                ?ResponseInterface $response = null,
                ?\Exception        $exception = null
            ) use ($config) {
                $maxRetries = $config['max_retries'] ?? 3;
                if ($retries >= $maxRetries) {
                    return false;
                }

                // 只在服务器错误或网络异常时重试
                if ($exception instanceof \GuzzleHttp\Exception\ConnectException) {
                    return true;
                }

                if ($response) {
                    return $response->getStatusCode() >= 500;
                }

                return false;
            },
            function (int $retries) use ($config) {
                return 1000 * $retries; // 指数退避
            }
        ));

        $defaultConfig = [
            'timeout'         => $config['timeout'] ?? 30,
            'connect_timeout' => $config['connect_timeout'] ?? 10,
            'http_errors'     => true,
            'handler'         => $stack,
        ];

        if (!empty($config['base_uri'])) {
            $defaultConfig['base_uri'] = $config['base_uri'];
        }

        return new Client($defaultConfig);
    }
}