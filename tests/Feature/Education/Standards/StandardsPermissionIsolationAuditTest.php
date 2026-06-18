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

namespace HyperfTests\Feature\Education\Standards;

use App\Http\Common\ResultCode;
use App\Model\Education\Foundation\EducationAuditLog;

/**
 * @internal
 * @coversNothing
 */
final class StandardsPermissionIsolationAuditTest extends StandardsApiCase
{
    public function testStandardMutationsRequirePermissionAndWriteAudit(): void
    {
        $fixture = $this->standardsFixture('standards_permission');
        $headers = $this->tenantHeaders($fixture['tenant'], ['X-Campus-Id' => (string) $fixture['campus_id']]);
        $payload = [
            'package_code' => 'PERM-BASIC',
            'package_name' => 'Permission Basic',
            'course_id' => $fixture['course_id'],
        ];

        $denied = $this->post('/admin/education/standards/service-packages', $payload, $headers);
        self::assertSame(ResultCode::FORBIDDEN->value, $denied['code']);

        $this->grantPermissions('education:standards:package:save');
        $ok = $this->post('/admin/education/standards/service-packages', $payload, $headers);

        self::assertSame(ResultCode::SUCCESS->value, $ok['code']);
        self::assertSame(1, EducationAuditLog::query()->where('module', 'standards')->where('action', 'education.standards.package.saved')->count());
    }
}
