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

use App\Event\Education\Foundation\EducationAuditEvent;
use App\Model\Education\Foundation\EducationAuditLog;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Foundation\AuditLogger;
use App\Service\Education\Foundation\EducationUserContext;
use GuzzleHttp\Psr7\ServerRequest;
use Hyperf\Context\Context;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @internal
 * @coversNothing
 */
final class AuditLoggerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        EducationAuditLog::query()->whereRaw('1=1')->delete();
        Context::destroy(ServerRequestInterface::class);
    }

    protected function tearDown(): void
    {
        Context::destroy(ServerRequestInterface::class);
        parent::tearDown();
    }

    public function testRecordCreatesAuditLogFromEvent(): void
    {
        Context::set(ServerRequestInterface::class, new ServerRequest(
            'PUT',
            '/admin/education/foundation/campuses/2001',
            [
                'X-Request-Id' => 'req-audit-001',
                'X-Forwarded-For' => '10.0.0.1, 10.0.0.2',
                'User-Agent' => 'MineAdminTest/1.0',
            ],
            null,
            '1.1',
            ['REMOTE_ADDR' => '127.0.0.1']
        ));

        $logger = make(AuditLogger::class);
        $log = $logger->record(new EducationAuditEvent(
            module: 'foundation',
            resource: 'campus',
            action: 'education.foundation.campus.updated',
            businessType: 'campus',
            businessId: 2001,
            context: $this->context(),
            beforeSnapshot: ['name' => 'Campus East'],
            afterSnapshot: ['name' => 'Campus East Plus'],
            metadata: ['campus_id' => 2001],
            summary: 'Campus East updated'
        ));

        self::assertSame('foundation', $log->module);
        self::assertSame('education.foundation.campus.updated', $log->action);
        self::assertSame('2001', $log->business_id);
        self::assertSame(1001, $log->tenant_id);
        self::assertSame(2001, $log->campus_id);
        self::assertSame(501, $log->actor_user_id);
        self::assertSame('admin', $log->actor_type);
        self::assertSame('tenant_admin', $log->actor_role_code);
        self::assertSame('req-audit-001', $log->request_id);
        self::assertSame('10.0.0.1', $log->ip_address);
        self::assertSame('MineAdminTest/1.0', $log->user_agent);
        self::assertSame('PUT', $log->method);
        self::assertSame('/admin/education/foundation/campuses/2001', $log->path);
        self::assertSame(['name' => ['before' => 'Campus East', 'after' => 'Campus East Plus']], $log->diff);
    }

    public function testSensitiveFieldsAreRemovedOrMasked(): void
    {
        $logger = make(AuditLogger::class);
        $log = $logger->record(new EducationAuditEvent(
            module: 'foundation',
            resource: 'user_profile',
            action: 'education.foundation.user_profile.updated',
            businessType: 'user_profile',
            businessId: 6001,
            context: $this->context(),
            afterSnapshot: [
                'name' => 'Demo',
                'password' => 'secret',
                'access_token' => 'token',
                'openid' => 'openid-1',
                'phone' => '13812345678',
                'email' => 'demo@example.com',
            ]
        ));

        self::assertArrayHasKey('name', $log->after_snapshot);
        self::assertArrayNotHasKey('password', $log->after_snapshot);
        self::assertArrayNotHasKey('access_token', $log->after_snapshot);
        self::assertArrayNotHasKey('openid', $log->after_snapshot);
        self::assertSame('138****5678', $log->after_snapshot['phone']);
        self::assertSame('d***@example.com', $log->after_snapshot['email']);
    }

    public function testDiffContainsOnlyChangedSafeFields(): void
    {
        $logger = make(AuditLogger::class);
        $log = $logger->record(new EducationAuditEvent(
            module: 'foundation',
            resource: 'tenant',
            action: 'education.foundation.tenant.updated',
            businessType: 'tenant',
            businessId: 1001,
            context: $this->context(),
            beforeSnapshot: [
                'name' => 'Old Tenant',
                'status' => 'enabled',
                'password_hash' => 'old-secret',
            ],
            afterSnapshot: [
                'name' => 'New Tenant',
                'status' => 'enabled',
                'password_hash' => 'new-secret',
            ]
        ));

        self::assertSame([
            'name' => [
                'before' => 'Old Tenant',
                'after' => 'New Tenant',
            ],
        ], $log->diff);
    }

    private function context(): EducationUserContext
    {
        return new EducationUserContext(
            userId: 501,
            tenantId: 1001,
            roleCode: EducationRoleCode::TenantAdmin,
            platformAccess: false,
            campusIds: [],
            currentCampusId: null
        );
    }
}
