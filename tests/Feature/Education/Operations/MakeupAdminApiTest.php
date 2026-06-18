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

namespace HyperfTests\Feature\Education\Operations;

use App\Http\Common\ResultCode;
use App\Model\Education\Operations\EducationMakeupEntitlement;

/**
 * @internal
 * @coversNothing
 */
final class MakeupAdminApiTest extends OperationApiCase
{
    public function testArrangeMakeupRejectsExpiredEntitlement(): void
    {
        $this->grantPermissions('education:operations:makeup:arrange');
        $fixture = $this->fixture('ops_makeup_api');
        $this->createTenantProfile($fixture['tenant']);
        $entitlement = EducationMakeupEntitlement::query()->create(['tenant_id' => $fixture['tenant']->id, 'campus_id' => $fixture['campus']->id, 'student_id' => 1, 'course_id' => $fixture['course']->id, 'source_lesson_id' => $fixture['lesson']->id, 'source_leave_request_id' => 1, 'status' => 'expired']);
        $result = $this->post('/admin/education/operations/makeup-entitlements/' . $entitlement->id . '/arrange', ['makeup_lesson_id' => $fixture['lesson']->id, 'arranged_at' => '2026-06-12 10:00:00'], $this->tenantHeaders($fixture['tenant']));

        self::assertSame(ResultCode::CONFLICT->value, $result['code']);
        self::assertSame('makeup entitlement is expired', $result['message']);
    }
}
