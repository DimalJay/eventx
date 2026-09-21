<?php

namespace Tests\Helpers;

use Helpers\APIResponse;
use Tests\TestCase;

class APIResponseTest extends TestCase
{
    public function testSuccessReturnsDefaultPayloadAndHttp200(): void
    {
        $result = APIResponse::success();

        $this->assertSame(200, http_response_code());
        $this->assertTrue($result['success']);
        $this->assertSame('Success', $result['message']);
        $this->assertNull($result['data']);
    }

    public function testSuccessWithMessageAndData(): void
    {
        $result = APIResponse::success('Created', ['id' => 1]);

        $this->assertSame('Created', $result['message']);
        $this->assertSame(['id' => 1], $result['data']);
    }

    public function testErrorSetsProvidedCode(): void
    {
        APIResponse::error('Bad request', 422, ['field' => 'invalid']);

        $this->assertSame(422, http_response_code());
        $this->assertFalse(APIResponse::error('Bad request', 422, ['field' => 'invalid'])['success']);
    }

    public function testErrorUsesDefaultCodeWhenNoneProvided(): void
    {
        $result = APIResponse::error('Something went wrong');

        $this->assertSame(400, http_response_code());
        $this->assertFalse($result['success']);
        $this->assertSame('Something went wrong', $result['message']);
        $this->assertNull($result['data']);
    }

    public function testErrorOverwritesPreviousStatusCode(): void
    {
        APIResponse::success('ok');
        APIResponse::error('nope', 500);

        $this->assertSame(500, http_response_code());
    }
}