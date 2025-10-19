<?php

namespace Leanku\Ai\Tests\Requests;

use Leanku\Ai\Requests\EmbeddingRequest;
use Leanku\Ai\Tests\TestCase;

class EmbeddingRequestTest extends TestCase
{
    public function testEmbeddingRequestCreation()
    {
        $input = ['The quick brown fox'];
        $request = new EmbeddingRequest($input, 'text-embedding-ada-002');

        $this->assertEquals($input, $request->getInput());
        $this->assertEquals('text-embedding-ada-002', $request->getModel());
    }

    public function testWithModel()
    {
        $request = new EmbeddingRequest([], null);
        $request->withModel('custom-model');

        $this->assertEquals('custom-model', $request->getModel());
    }
}