<?php

declare(strict_types=1);

namespace HyperfTests\Feature\Education\Operations;

use App\Http\Common\ResultCode;

final class OperationPermissionIsolationAuditTest extends OperationApiCase
{
    public function testHighRiskButtonsRequirePermissions(): void
    {
        $fixture = $this->fixture('ops_permission_api');
        $this->createTenantProfile($fixture['tenant']);
        $result = $this->post('/admin/education/operations/lessons/batch-change', ['lesson_ids' => [$fixture['lesson']->id], 'change_type' => 'cancel', 'reason' => 'holiday'], $this->tenantHeaders($fixture['tenant']));

        self::assertSame(ResultCode::FORBIDDEN->value, $result['code']);
    }
}
