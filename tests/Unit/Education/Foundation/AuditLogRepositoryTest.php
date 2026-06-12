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

namespace HyperfTests\Unit\Education\Foundation;

use App\Model\Education\Foundation\EducationAuditLog;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Foundation\AuditLogRepository;
use App\Service\Education\Foundation\EducationUserContext;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class AuditLogRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        EducationAuditLog::query()->whereRaw('1=1')->delete();
    }

    public function testPlatformAdminCanFilterAllTenants(): void
    {
        $this->createLog(['tenant_id' => 1001, 'summary' => 'Tenant A']);
        $tenantB = $this->createLog(['tenant_id' => 1002, 'summary' => 'Tenant B']);

        $page = make(AuditLogRepository::class)->pageByContext([
            'page' => 1,
            'pageSize' => 20,
            'tenant_id' => 1002,
        ], $this->platformContext());

        self::assertSame(1, $page['total']);
        self::assertSame((int) $tenantB->id, (int) $page['list'][0]->id);
    }

    public function testTenantAdminReadsOnlyOwnTenant(): void
    {
        $tenantA = $this->createLog(['tenant_id' => 1001, 'summary' => 'Tenant A']);
        $this->createLog(['tenant_id' => 1002, 'summary' => 'Tenant B']);

        $page = make(AuditLogRepository::class)->pageByContext([
            'page' => 1,
            'pageSize' => 20,
        ], $this->tenantContext(1001));

        self::assertSame(1, $page['total']);
        self::assertSame((int) $tenantA->id, (int) $page['list'][0]->id);
    }

    public function testCampusScopedRoleReadsTenantWideAndAllowedCampusRows(): void
    {
        $tenantWide = $this->createLog(['tenant_id' => 1001, 'campus_id' => null, 'summary' => 'Tenant wide']);
        $allowed = $this->createLog(['tenant_id' => 1001, 'campus_id' => 2001, 'summary' => 'Allowed campus']);
        $this->createLog(['tenant_id' => 1001, 'campus_id' => 2002, 'summary' => 'Other campus']);
        $this->createLog(['tenant_id' => 1002, 'campus_id' => 2001, 'summary' => 'Other tenant']);

        $page = make(AuditLogRepository::class)->pageByContext([
            'page' => 1,
            'pageSize' => 20,
        ], $this->campusContext());

        $ids = array_map(static fn (EducationAuditLog $log): int => (int) $log->id, $page['list']);
        sort($ids);

        self::assertSame(2, $page['total']);
        self::assertSame([(int) $tenantWide->id, (int) $allowed->id], $ids);
    }

    private function createLog(array $overrides = []): EducationAuditLog
    {
        /** @var EducationAuditLog $log */
        $log = EducationAuditLog::query()->create(array_merge([
            'tenant_id' => 1001,
            'campus_id' => null,
            'actor_user_id' => 501,
            'actor_type' => 'admin',
            'actor_role_code' => 'tenant_admin',
            'module' => 'foundation',
            'resource' => 'tenant',
            'action' => 'education.foundation.tenant.updated',
            'business_type' => 'tenant',
            'business_id' => '1001',
            'summary' => 'Tenant updated',
            'created_at' => '2026-06-10 10:00:00',
        ], $overrides));

        return $log;
    }

    private function platformContext(): EducationUserContext
    {
        return new EducationUserContext(501, null, EducationRoleCode::PlatformOperator, true, [], null);
    }

    private function tenantContext(int $tenantId): EducationUserContext
    {
        return new EducationUserContext(501, $tenantId, EducationRoleCode::TenantAdmin, false, [], null);
    }

    private function campusContext(): EducationUserContext
    {
        return new EducationUserContext(501, 1001, EducationRoleCode::AcademicStaff, false, [2001], 2001);
    }
}
