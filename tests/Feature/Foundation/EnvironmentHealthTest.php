<?php

declare(strict_types=1);

namespace HyperfTests\Feature\Foundation;

use HyperfTests\HttpTestCase;

final class EnvironmentHealthTest extends HttpTestCase
{
    public function test_health_endpoint_returns_success_result_shape(): void
    {
        $response = $this->get('/health');

        self::assertSame(200, $response['code']);
        self::assertSame('success', $response['message']);
        self::assertSame('mineadmin-education-saas', $response['data']['app']);
        self::assertSame('ok', $response['data']['database']);
        self::assertSame('ok', $response['data']['redis']);
    }
}
