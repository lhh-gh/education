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
use App\Model\Education\Operations\EducationMakeupRecord;

/**
 * @internal
 * @coversNothing
 */
final class MakeupAdminApiTest extends OperationApiCase
{
    public function testPlatformUserCanPageMakeupEntitlementsAndRecordsWithoutTenantHeader(): void
    {
        $this->grantPermissions('education:operations:makeup:page');
        $fixture = $this->fixture('ops_makeup_page_platform');
        $this->createEducationProfile();
        $entitlement = EducationMakeupEntitlement::query()->create(['tenant_id' => $fixture['tenant']->id, 'campus_id' => $fixture['campus']->id, 'student_id' => 1, 'course_id' => $fixture['course']->id, 'source_lesson_id' => $fixture['lesson']->id, 'source_leave_request_id' => 1, 'status' => 'available']);
        $record = EducationMakeupRecord::query()->create(['tenant_id' => $fixture['tenant']->id, 'campus_id' => $fixture['campus']->id, 'makeup_entitlement_id' => $entitlement->id, 'student_id' => 1, 'makeup_lesson_id' => $fixture['lesson']->id, 'status' => 'arranged', 'arranged_by' => $this->user->id, 'arranged_at' => '2026-06-12 10:00:00']);

        $entitlements = $this->get('/admin/education/operations/makeup-entitlements/page?page=1&pageSize=20&status=available', [], $this->authHeaders());
        $records = $this->get('/admin/education/operations/makeup-records/page?page=1&pageSize=20', [], $this->authHeaders());

        self::assertSame(ResultCode::SUCCESS->value, $entitlements['code']);
        self::assertSame(1, $entitlements['data']['total']);
        self::assertSame($entitlement->id, $entitlements['data']['list'][0]['id']);
        self::assertSame(ResultCode::SUCCESS->value, $records['code']);
        self::assertSame(1, $records['data']['total']);
        self::assertSame($record->id, $records['data']['list'][0]['id']);
    }

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
