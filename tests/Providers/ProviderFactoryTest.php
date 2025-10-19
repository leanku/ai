<?php

namespace Leanku\Ai\Tests\Providers;

use Leanku\Ai\Providers\ProviderFactory;
use Leanku\Ai\Providers\OpenAIProvider;
use Leanku\Ai\Tests\TestCase;
use InvalidArgumentException;

class ProviderFactoryTest extends TestCase
{
    public function testCreateOpenAIProvider()
    {
        $config = $this->getConfig()['providers']['openai'];
        $provider = ProviderFactory::create('openai', $config);

        $this->assertInstanceOf(OpenAIProvider::class, $provider);
        $this->assertEquals('openai', $provider->getName());
    }

    public function testCreateWithInvalidProvider()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported provider: invalid');

        ProviderFactory::create('invalid', []);
    }

    public function testRegisterCustomProvider()
    {
        $mockProviderClass = get_class(new class extends OpenAIProvider {
            public function getName(): string { return 'custom'; }
        });

        ProviderFactory::register('custom', $mockProviderClass);

        $provider = ProviderFactory::create('custom', []);
        $this->assertInstanceOf($mockProviderClass, $provider);
        $this->assertEquals('custom', $provider->getName());
    }

    public function testRegisterInvalidProvider()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Provider class must implement ProviderInterface');

        ProviderFactory::register('invalid', \stdClass::class);
    }

    public function testGetAvailableProviders()
    {
        $providers = ProviderFactory::getAvailableProviders();

        $this->assertContains('openai', $providers);
        $this->assertIsArray($providers);
    }
}