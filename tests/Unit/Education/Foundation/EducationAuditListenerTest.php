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
use App\Listener\Education\Foundation\EducationAuditListener;
use App\Model\Education\Foundation\EducationAuditLog;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\Context\Context;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @internal
 * @coversNothing
 */
final class EducationAuditListenerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        EducationAuditLog::query()->whereRaw('1=1')->delete();
        Context::destroy(ServerRequestInterface::class);
    }

    public function testListenerRecordsEducationAuditEvent(): void
    {
        $listener = make(EducationAuditListener::class);

        $listener->process(new EducationAuditEvent(
            module: 'foundation',
            resource: 'tenant',
            action: 'education.foundation.tenant.created',
            businessType: 'tenant',
            businessId: 1001,
            context: $this->context(),
            afterSnapshot: ['name' => 'Demo Tenant']
        ));

        self::assertSame([EducationAuditEvent::class], $listener->listen());
        self::assertSame(1, EducationAuditLog::query()->where('action', 'education.foundation.tenant.created')->count());
    }

    public function testListenerIgnoresOtherEventObjects(): void
    {
        $listener = make(EducationAuditListener::class);

        $listener->process(new class {});

        self::assertSame(0, EducationAuditLog::query()->count());
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
