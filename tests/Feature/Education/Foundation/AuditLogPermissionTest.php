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

use App\Http\Admin\Controller\Education\Foundation\AuditLogController;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\ResultCode;
use App\Model\Education\Foundation\EducationAuditLog;
use Hyperf\HttpServer\Annotation\Middleware;

/**
 * @internal
 * @coversNothing
 */
final class AuditLogPermissionTest extends EducationAdminControllerCase
{
    public function testPageRequiresPermission(): void
    {
        $result = $this->get('/admin/education/foundation/audit-logs/page', [
            'token' => $this->token,
            'page' => 1,
            'pageSize' => 20,
        ]);

        self::assertSame(ResultCode::FORBIDDEN->value, $result['code']);
    }

    public function testDetailRequiresPermission(): void
    {
        $log = $this->createLog();

        $result = $this->get('/admin/education/foundation/audit-logs/' . $log->id, [
            'token' => $this->token,
        ]);

        self::assertSame(ResultCode::FORBIDDEN->value, $result['code']);
    }

    public function testAuditLogControllerDoesNotUseOperationMiddleware(): void
    {
        foreach ((new \ReflectionClass(AuditLogController::class))->getAttributes(Middleware::class) as $attribute) {
            self::assertNotSame(OperationMiddleware::class, $attribute->getArguments()['middleware'] ?? null);
        }
    }

    private function createLog(): EducationAuditLog
    {
        /* @var EducationAuditLog $log */
        return EducationAuditLog::query()->create([
            'tenant_id' => 1001,
            'actor_type' => 'admin',
            'module' => 'foundation',
            'resource' => 'tenant',
            'action' => 'education.foundation.tenant.updated',
            'business_type' => 'tenant',
            'business_id' => '1001',
            'created_at' => '2026-06-10 10:00:00',
        ]);
    }
}
