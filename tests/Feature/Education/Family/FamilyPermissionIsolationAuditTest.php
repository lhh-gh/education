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

namespace HyperfTests\Feature\Education\Family;

use App\Http\Common\ResultCode;
use App\Model\Education\Foundation\EducationAuditLog;

/**
 * @internal
 * @coversNothing
 */
final class FamilyPermissionIsolationAuditTest extends FamilyApiCase
{
    public function testFamilyWritesRequirePermissionAndWriteAudit(): void
    {
        $fixture = $this->familyFixture('family_audit_api', 'tenant_admin');
        $headers = $this->tenantHeaders($fixture['tenant'], ['X-Campus-Id' => (string) $fixture['campus_id']]);
        $payload = [
            'title' => 'Unit 1 practice',
            'content' => 'Finish worksheet',
            'class_id' => $fixture['class_id'],
            'lesson_id' => $fixture['lesson_id'],
            'student_ids' => [$fixture['student_id']],
        ];

        $denied = $this->post('/admin/education/family/homework-assignments', $payload, $headers);
        self::assertSame(ResultCode::FORBIDDEN->value, $denied['code']);

        $this->grantPermissions('education:family:homework:create');
        $allowed = $this->post('/admin/education/family/homework-assignments', $payload, $headers);

        self::assertSame(ResultCode::SUCCESS->value, $allowed['code']);
        self::assertSame(1, EducationAuditLog::query()->where('module', 'family')->where('action', 'education.family.homework.published')->count());
    }
}
