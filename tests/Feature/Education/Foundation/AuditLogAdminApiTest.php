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

/**
 * @internal
 * @coversNothing
 */
final class AuditLogAdminApiTest extends EducationAdminControllerCase
{
    public function testPageReturnsMineadminResultShape(): void
    {
        $this->createEducationProfile();
        $this->grantPermissions('education:foundation:audit-log:page');
        $this->createLog([
            'resource' => 'campus',
            'business_type' => 'campus',
            'business_id' => '2001',
            'summary' => 'Campus East updated',
            'before_snapshot' => ['name' => 'Campus East'],
        ]);

        $result = $this->get('/admin/education/foundation/audit-logs/page', [
            'token' => $this->token,
            'page' => 1,
            'pageSize' => 20,
            'module' => 'foundation',
            'keyword' => 'Campus',
        ]);

        self::assertSame(ResultCode::SUCCESS->value, $result['code']);
        self::assertSame(1, $result['data']['total']);
        self::assertSame('foundation', $result['data']['list'][0]['module']);
        self::assertArrayNotHasKey('before_snapshot', $result['data']['list'][0]);
        self::assertArrayNotHasKey('after_snapshot', $result['data']['list'][0]);
        self::assertArrayNotHasKey('diff', $result['data']['list'][0]);
        self::assertArrayNotHasKey('metadata', $result['data']['list'][0]);
    }

    public function testDetailReturnsPayload(): void
    {
        $this->createEducationProfile();
        $this->grantPermissions('education:foundation:audit-log:detail');
        $log = $this->createLog([
            'before_snapshot' => ['name' => 'Campus East'],
            'after_snapshot' => ['name' => 'Campus East Plus'],
            'diff' => ['name' => ['before' => 'Campus East', 'after' => 'Campus East Plus']],
            'metadata' => ['campus_id' => 2001],
        ]);

        $result = $this->get('/admin/education/foundation/audit-logs/' . $log->id, [
            'token' => $this->token,
        ]);

        self::assertSame(ResultCode::SUCCESS->value, $result['code']);
        self::assertSame(['name' => 'Campus East'], $result['data']['before_snapshot']);
        self::assertSame(['name' => 'Campus East Plus'], $result['data']['after_snapshot']);
        self::assertSame('Campus East', $result['data']['diff']['name']['before']);
        self::assertSame('Campus East Plus', $result['data']['diff']['name']['after']);
        self::assertSame(['campus_id' => 2001], $result['data']['metadata']);
    }

    public function testInvalidDateRangeReturns422(): void
    {
        $this->createEducationProfile();
        $this->grantPermissions('education:foundation:audit-log:page');

        $result = $this->get('/admin/education/foundation/audit-logs/page', [
            'token' => $this->token,
            'page' => 1,
            'pageSize' => 20,
            'start_at' => '2026-06-11 00:00:00',
            'end_at' => '2026-06-10 00:00:00',
        ]);

        self::assertSame(ResultCode::UNPROCESSABLE_ENTITY->value, $result['code']);
        self::assertSame('end_at must be greater than or equal to start_at', $result['message']);
    }

    private function createLog(array $overrides = []): EducationAuditLog
    {
        /* @var EducationAuditLog $log */
        return EducationAuditLog::query()->create(array_merge([
            'tenant_id' => 1001,
            'campus_id' => null,
            'actor_user_id' => $this->user->id,
            'actor_type' => 'admin',
            'actor_role_code' => 'tenant_admin',
            'module' => 'foundation',
            'resource' => 'tenant',
            'action' => 'education.foundation.tenant.updated',
            'business_type' => 'tenant',
            'business_id' => '1001',
            'request_id' => 'req-audit-api',
            'ip_address' => '127.0.0.1',
            'method' => 'PUT',
            'path' => '/admin/education/foundation/tenants/1001',
            'summary' => 'Tenant updated',
            'created_at' => '2026-06-10 10:00:00',
        ], $overrides));
    }
}
