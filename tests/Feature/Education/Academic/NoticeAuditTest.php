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

namespace HyperfTests\Feature\Education\Academic;

use App\Model\Education\Foundation\EducationAuditLog;

/**
 * @internal
 * @coversNothing
 */
final class NoticeAuditTest extends ProfileRecordAdminCase
{
    use NoticeAdminApiFixture;

    public function testCreateUpdatePublishWithdrawCreateAuditLogs(): void
    {
        $this->grantPermissions('education:academic:notice:create', 'education:academic:notice:update', 'education:academic:notice:publish', 'education:academic:notice:withdraw');
        $fixture = $this->noticeFixture('notice_audit');
        $headers = $this->tenantHeaders($fixture['tenant']);

        $created = $this->post('/admin/education/academic/notices', $this->noticePayload($fixture), $headers);
        $this->put('/admin/education/academic/notices/' . $created['data']['id'], array_merge($this->noticePayload($fixture), ['title' => 'Updated reminder']), $headers);
        $this->put('/admin/education/academic/notices/' . $created['data']['id'] . '/publish', [], $headers);
        $this->put('/admin/education/academic/notices/' . $created['data']['id'] . '/withdraw', ['withdraw_reason' => 'audit'], $headers);

        self::assertTrue(EducationAuditLog::query()->where('action', 'education.academic.notice.created')->exists());
        self::assertTrue(EducationAuditLog::query()->where('action', 'education.academic.notice.updated')->exists());
        self::assertTrue(EducationAuditLog::query()->where('action', 'education.academic.notice.published')->exists());
        self::assertTrue(EducationAuditLog::query()->where('action', 'education.academic.notice.withdrawn')->exists());
    }
}
