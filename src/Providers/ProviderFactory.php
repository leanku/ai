<?php
declare(strict_types=1);

namespace Leanku\Ai\Providers;

use Leanku\Ai\Contracts\ProviderInterface;
use Leanku\Ai\Exceptions\ProviderException;
use InvalidArgumentException;

class ProviderFactory
{
    private static array $providers = [
        'openai'   => OpenAIProvider::class,
        'deepseek' => DeepSeekProvider::class,
        'ollama'   => OllamaProvider::class,
    ];

    public static function create(string $provider, array $config = []): ProviderInterface
    {
        $provider = strtolower($provider);

        if (!isset(self::$providers[$provider])) {
            throw new InvalidArgumentException("Unsupported provider: {$provider}. Available: " . implode(', ', array_keys(self::$providers)));
        }

        $providerClass = self::$providers[$provider];

        try {
            return new $providerClass($config);
        } catch (\Exception $e) {
            throw new ProviderException("Failed to create provider {$provider}: " . $e->getMessage(), 0, $e);
        }
    }

    public static function register(string $name, string $providerClass): void
    {
        if (!is_subclass_of($providerClass, ProviderInterface::class)) {
            throw new InvalidArgumentException("Provider class must implement ProviderInterface");
        }

        self::$providers[strtolower($name)] = $providerClass;
    }

    public static function getAvailableProviders(): array
    {
        return array_keys(self::$providers);
    }

}