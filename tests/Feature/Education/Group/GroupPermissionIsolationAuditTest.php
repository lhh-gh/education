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

namespace HyperfTests\Feature\Education\Group;

use App\Http\Common\ResultCode;
use App\Model\Education\Foundation\EducationAuditLog;

/**
 * @internal
 * @coversNothing
 */
final class GroupPermissionIsolationAuditTest extends GroupApiCase
{
    public function testGroupMutationsRequirePermissionAndWriteAudit(): void
    {
        $fixture = $this->groupFixture('group_audit_api');
        $this->createTenantProfile($fixture['tenant'], 'tenant_admin', $fixture['campus']);

        $payload = [
            'code' => 'HQ',
            'name' => 'Headquarters',
            'unit_type' => 'group',
        ];
        $denied = $this->post('/admin/education/group/org-units', $payload, $this->tenantHeaders($fixture['tenant']));
        self::assertSame(ResultCode::FORBIDDEN->value, $denied['code']);

        $this->grantPermissions('education:group:org:create');
        $allowed = $this->post('/admin/education/group/org-units', $payload, $this->tenantHeaders($fixture['tenant'], ['X-Campus-Id' => (string) $fixture['campus']->id]));
        self::assertSame(ResultCode::SUCCESS->value, $allowed['code']);
        self::assertSame(1, EducationAuditLog::query()->where('module', 'group')->where('action', 'education.group.org.saved')->count());
    }
}
