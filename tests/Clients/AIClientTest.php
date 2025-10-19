<?php

namespace Leanku\Ai\Tests\Clients;

use Leanku\Ai\Clients\AIClient;
use Leanku\Ai\Tests\TestCase;
use Leanku\Ai\Builders\MessageBuilder;

class AIClientTest extends TestCase
{
    public function testAIClientCreation()
    {
        $client = new AIClient($this->getConfig());

        $this->assertInstanceOf(AIClient::class, $client);
        $this->assertEquals('openai', $client->getProvider()->getName());
    }

    public function testWithProvider()
    {
        $client = new AIClient($this->getConfig());
        $newClient = $client->withProvider('openai', ['api_key' => 'custom-key']);

        $this->assertInstanceOf(AIClient::class, $newClient);
        $this->assertNotSame($client, $newClient); // 应该是新实例
    }

    public function testAskMethodReturnsMessageBuilder()
    {
        $client = new AIClient($this->getConfig());
        $builder = $client->ask('Hello');

        $this->assertInstanceOf(MessageBuilder::class, $builder);
    }

    public function testChat()
    {
        $client = new AIClient($this->getConfig());
        $response = $client->chat([
            ['role' => 'user', 'content' => 'Hello, AI!']
        ]);
        echo $response->getContent();

    }
}