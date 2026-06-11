<?php

declare(strict_types=1);

namespace HyperfTests\Unit\Education\Foundation;

use App\Http\Common\ResultCode;
use PHPUnit\Framework\TestCase;

final class TenantServiceTest extends TestCase
{
    public function test_conflict_result_code_is_available_for_duplicate_tenant_failures(): void
    {
        self::assertSame(409, ResultCode::CONFLICT->value);
    }
}
