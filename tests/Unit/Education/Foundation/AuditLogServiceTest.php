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

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Foundation\EducationAuditLog;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Foundation\AuditLogService;
use App\Service\Education\Foundation\EducationUserContext;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class AuditLogServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        EducationAuditLog::query()->whereRaw('1=1')->delete();
    }

    public function testPageHidesPayloadFields(): void
    {
        $this->createLog([
            'before_snapshot' => ['name' => 'Old'],
            'after_snapshot' => ['name' => 'New'],
            'diff' => ['name' => ['before' => 'Old', 'after' => 'New']],
            'metadata' => ['tenant_id' => 1001],
        ]);

        $page = make(AuditLogService::class)->page([
            'page' => 1,
            'pageSize' => 20,
        ], $this->tenantContext(1001));

        self::assertSame(1, $page['total']);
        self::assertArrayNotHasKey('before_snapshot', $page['list'][0]);
        self::assertArrayNotHasKey('after_snapshot', $page['list'][0]);
        self::assertArrayNotHasKey('diff', $page['list'][0]);
        self::assertArrayNotHasKey('metadata', $page['list'][0]);
    }

    public function testDetailReturnsPayloadFields(): void
    {
        $log = $this->createLog([
            'before_snapshot' => ['name' => 'Old'],
            'after_snapshot' => ['name' => 'New'],
            'diff' => ['name' => ['before' => 'Old', 'after' => 'New']],
            'metadata' => ['tenant_id' => 1001],
        ]);

        $detail = make(AuditLogService::class)->detail((int) $log->id, $this->tenantContext(1001));

        self::assertSame(['name' => 'Old'], $detail['before_snapshot']);
        self::assertSame(['name' => 'New'], $detail['after_snapshot']);
        self::assertSame('Old', $detail['diff']['name']['before']);
        self::assertSame('New', $detail['diff']['name']['after']);
        self::assertSame(['tenant_id' => 1001], $detail['metadata']);
    }

    public function testDetailThrowsNotFoundForInvisibleRow(): void
    {
        $log = $this->createLog(['tenant_id' => 1002]);

        try {
            make(AuditLogService::class)->detail((int) $log->id, $this->tenantContext(1001));
            self::fail('Expected invisible audit log to throw BusinessException.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::NOT_FOUND, $exception->getResponse()->code);
            self::assertSame('audit log not found', $exception->getResponse()->message);
        }
    }

    private function createLog(array $overrides = []): EducationAuditLog
    {
        /* @var EducationAuditLog $log */
        return EducationAuditLog::query()->create(array_merge([
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
    }

    private function tenantContext(int $tenantId): EducationUserContext
    {
        return new EducationUserContext(501, $tenantId, EducationRoleCode::TenantAdmin, false, [], null);
    }
}
