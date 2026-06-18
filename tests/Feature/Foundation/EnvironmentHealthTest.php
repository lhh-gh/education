<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */

namespace HyperfTests\Feature\Foundation;

use HyperfTests\HttpTestCase;

/**
 * @internal
 * @coversNothing
 */
final class EnvironmentHealthTest extends HttpTestCase
{
    public function testHealthEndpointReturnsSuccessResultShape(): void
    {
        $response = $this->get('/health');

        self::assertSame(200, $response['code']);
        self::assertSame('success', $response['message']);
        self::assertSame('mineadmin-education-saas', $response['data']['app']);
        self::assertSame('ok', $response['data']['database']);
        self::assertSame('ok', $response['data']['redis']);
    }
}
