<?php

namespace Leanku\Ai\Tests\Builders;

use Leanku\Ai\Builders\MessageBuilder;
use Leanku\Ai\Clients\AIClient;
use Leanku\Ai\Tests\TestCase;

class MessageBuilderTest extends TestCase
{
    private function createMockAIClient()
    {
        return $this->createMock(AIClient::class);
    }

    public function testMessageBuilderCreation()
    {
        $mockClient = $this->createMockAIClient();
        $builder = new MessageBuilder($mockClient, 'Hello', 'gpt-4');

        // 使用反射访问私有属性来验证
        $reflection = new \ReflectionClass($builder);
        $messagesProperty = $reflection->getProperty('messages');
        $messagesProperty->setAccessible(true);

        $messages = $messagesProperty->getValue($builder);

        $this->assertEquals([['role' => 'user', 'content' => 'Hello']], $messages);
    }

    public function testFromSystem()
    {
        $mockClient = $this->createMockAIClient();
        $builder = new MessageBuilder($mockClient, 'User message');
        $builder->fromSystem('System message');

        $reflection = new \ReflectionClass($builder);
        $messagesProperty = $reflection->getProperty('messages');
        $messagesProperty->setAccessible(true);

        $messages = $messagesProperty->getValue($builder);

        $this->assertEquals([
            ['role' => 'system', 'content' => 'System message'],
            ['role' => 'user', 'content' => 'User message'],
        ], $messages);
    }

    public function testAndThen()
    {
        $mockClient = $this->createMockAIClient();
        $builder = new MessageBuilder($mockClient, 'First message');
        $builder->andThen('Second message');

        $reflection = new \ReflectionClass($builder);
        $messagesProperty = $reflection->getProperty('messages');
        $messagesProperty->setAccessible(true);

        $messages = $messagesProperty->getValue($builder);

        $this->assertEquals([
            ['role' => 'user', 'content' => 'First message'],
            ['role' => 'user', 'content' => 'Second message'],
        ], $messages);
    }

    public function testWithOptions()
    {
        $mockClient = $this->createMockAIClient();
        $builder = new MessageBuilder($mockClient, 'Hello');
        $builder->withTemperature(0.8)->withMaxTokens(500);

        $reflection = new \ReflectionClass($builder);
        $optionsProperty = $reflection->getProperty('options');
        $optionsProperty->setAccessible(true);

        $options = $optionsProperty->getValue($builder);

        $this->assertEquals(0.8, $options['temperature']);
        $this->assertEquals(500, $options['maxTokens']);
    }
}