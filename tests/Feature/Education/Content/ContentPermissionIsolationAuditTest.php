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

namespace HyperfTests\Feature\Education\Content;

use App\Http\Common\ResultCode;
use App\Model\Education\Foundation\EducationAuditLog;

/**
 * @internal
 * @coversNothing
 */
final class ContentPermissionIsolationAuditTest extends ContentApiCase
{
    public function testContentMutationsRequirePermissionAndWriteAudit(): void
    {
        $fixture = $this->contentFixture('content_permission');
        $headers = $this->tenantHeaders($fixture['tenant'], ['X-Campus-Id' => (string) $fixture['campus_id']]);
        $payload = [
            'material_code' => 'PERM-CONTENT-001',
            'material_name' => 'Permission Content',
            'course_id' => $fixture['course_id'],
            'material_type' => 'worksheet',
            'guardian_visible' => true,
        ];

        $denied = $this->post('/admin/education/content/materials', $payload, $headers);
        self::assertSame(ResultCode::FORBIDDEN->value, $denied['code']);

        $this->grantPermissions('education:content:material:save');
        $allowed = $this->post('/admin/education/content/materials', $payload, $headers);

        self::assertSame(ResultCode::SUCCESS->value, $allowed['code']);
        self::assertSame(1, EducationAuditLog::query()->where('module', 'content')->where('action', 'education.content.material.saved')->count());
    }
}
