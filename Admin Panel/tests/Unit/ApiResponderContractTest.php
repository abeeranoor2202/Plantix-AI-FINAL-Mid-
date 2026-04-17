<?php

namespace Tests\Unit;

use App\Support\Api\ApiResponder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as LaravelPaginator;
use Tests\TestCase;

class ApiResponderContractTest extends TestCase
{
    private function responder(): object
    {
        return new class {
            use ApiResponder;

            public function okWrap(mixed $data = null)
            {
                return $this->ok($data, 'OK');
            }

            public function failWrap(string $message, mixed $errors = null, int $status = 400)
            {
                return $this->fail($message, $errors, $status);
            }

            public function pageWrap(LengthAwarePaginator $paginator, array $items)
            {
                return $this->paginated($paginator, $items);
            }
        };
    }

    /** @test */
    public function it_returns_standard_success_envelope(): void
    {
        $response = $this->responder()->okWrap(['x' => 1]);
        $payload = $response->getData(true);

        $this->assertTrue($payload['success']);
        $this->assertSame('OK', $payload['message']);
        $this->assertSame(['x' => 1], $payload['data']);
        $this->assertNull($payload['errors']);
    }

    /** @test */
    public function it_returns_standard_error_envelope(): void
    {
        $response = $this->responder()->failWrap('Invalid', ['field' => ['Required']], 422);
        $payload = $response->getData(true);

        $this->assertFalse($payload['success']);
        $this->assertSame('Invalid', $payload['message']);
        $this->assertNull($payload['data']);
        $this->assertSame(['field' => ['Required']], $payload['errors']);
        $this->assertSame(422, $response->getStatusCode());
    }

    /** @test */
    public function it_returns_standard_pagination_envelope(): void
    {
        $items = [['id' => 1], ['id' => 2]];
        $paginator = new LaravelPaginator($items, 12, 2, 2);

        $response = $this->responder()->pageWrap($paginator, $items);
        $payload = $response->getData(true);

        $this->assertTrue($payload['success']);
        $this->assertSame(2, $payload['data']['pagination']['page']);
        $this->assertSame(2, $payload['data']['pagination']['limit']);
        $this->assertSame(12, $payload['data']['pagination']['total']);
        $this->assertSame(6, $payload['data']['pagination']['last_page']);
    }
}
