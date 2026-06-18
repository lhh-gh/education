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

use App\Http\Common\ResultCode;
use App\Model\Education\Academic\EducationNotice;

/**
 * @internal
 * @coversNothing
 */
final class NoticePermissionTest extends ProfileRecordAdminCase
{
    use NoticeAdminApiFixture;

    public function testPublishRequiresPermission(): void
    {
        $fixture = $this->noticeFixture('notice_permission');
        $notice = EducationNotice::query()->create(array_merge($this->noticePayload($fixture), [
            'tenant_id' => $fixture['tenant_id'],
            'notice_no' => uniqid('NOT', false),
            'status' => 'draft',
        ]));

        $result = $this->put('/admin/education/academic/notices/' . $notice->id . '/publish', [], $this->tenantHeaders($fixture['tenant']));

        self::assertSame(ResultCode::FORBIDDEN->value, $result['code']);
    }
}
