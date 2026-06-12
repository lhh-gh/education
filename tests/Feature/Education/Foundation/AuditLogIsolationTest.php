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

namespace HyperfTests\Feature\Education\Foundation;

use App\Http\Common\ResultCode;
use App\Model\Education\Foundation\EducationAuditLog;
use App\Model\Education\Foundation\EducationTenant;
use App\Model\Education\Foundation\EducationUserCampusScope;

/**
 * @internal
 * @coversNothing
 */
final class AuditLogIsolationTest extends EducationAdminControllerCase
{
    public function testTenantAdminCannotReadOtherTenantDetail(): void
    {
        $tenantA = EducationTenant::query()->create(['name' => 'Tenant A', 'code' => 'tenant_a', 'status' => 'enabled']);
        $tenantB = EducationTenant::query()->create(['name' => 'Tenant B', 'code' => 'tenant_b', 'status' => 'enabled']);
        $this->createEducationProfile((int) $tenantA->id, 'tenant_admin');
        $this->grantPermissions('education:foundation:audit-log:detail');
        $log = $this->createLog(['tenant_id' => (int) $tenantB->id]);

        $result = $this->get('/admin/education/foundation/audit-logs/' . $log->id, [
            'token' => $this->token,
        ], ['X-Tenant-Id' => (string) $tenantA->id]);

        self::assertSame(ResultCode::NOT_FOUND->value, $result['code']);
        self::assertSame('audit log not found', $result['message']);
    }

    public function testCampusUserCannotReadOtherCampusDetail(): void
    {
        $tenant = EducationTenant::query()->create(['name' => 'Tenant A', 'code' => 'tenant_a', 'status' => 'enabled']);
        $profile = $this->createEducationProfile((int) $tenant->id, 'academic_staff');
        EducationUserCampusScope::query()->create([
            'tenant_id' => (int) $tenant->id,
            'user_profile_id' => (int) $profile->id,
            'user_id' => (int) $this->user->id,
            'campus_id' => 2001,
        ]);
        $this->grantPermissions('education:foundation:audit-log:detail');
        $log = $this->createLog(['tenant_id' => (int) $tenant->id, 'campus_id' => 2002]);

        $result = $this->get('/admin/education/foundation/audit-logs/' . $log->id, [
            'token' => $this->token,
        ], ['X-Tenant-Id' => (string) $tenant->id]);

        self::assertSame(ResultCode::NOT_FOUND->value, $result['code']);
        self::assertSame('audit log not found', $result['message']);
    }

    private function createLog(array $overrides = []): EducationAuditLog
    {
        /** @var EducationAuditLog $log */
        $log = EducationAuditLog::query()->create(array_merge([
            'tenant_id' => 1001,
            'campus_id' => null,
            'actor_type' => 'admin',
            'module' => 'foundation',
            'resource' => 'campus',
            'action' => 'education.foundation.campus.updated',
            'business_type' => 'campus',
            'business_id' => '2001',
            'created_at' => '2026-06-10 10:00:00',
        ], $overrides));

        return $log;
    }
}
