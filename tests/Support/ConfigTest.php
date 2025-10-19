<?php

namespace Leanku\Ai\Tests\Support;

use Leanku\Ai\Support\Config;
use Leanku\Ai\Tests\TestCase;

class ConfigTest extends TestCase
{
    public function testGetWithDotNotation()
    {
        $config = new Config([
            'database' => [
                'connection' => 'mysql',
                'host' => 'localhost',
            ],
        ]);

        $this->assertEquals('mysql', $config->get('database.connection'));
        $this->assertEquals('localhost', $config->get('database.host'));
    }

    public function testGetWithDefaultValue()
    {
        $config = new Config([]);

        $this->assertEquals('default', $config->get('non.existent.key', 'default'));
        $this->assertNull($config->get('non.existent.key'));
    }

    public function testHasMethod()
    {
        $config = new Config([
            'enabled' => true,
            'settings' => [
                'debug' => false,
            ],
        ]);

        $this->assertTrue($config->has('enabled'));
        $this->assertTrue($config->has('settings.debug'));
        $this->assertFalse($config->has('non.existent'));
        $this->assertFalse($config->has('settings.non.existent'));
    }

    public function testAllMethod()
    {
        $data = ['key' => 'value'];
        $config = new Config($data);

        $this->assertEquals($data, $config->all());
    }
}