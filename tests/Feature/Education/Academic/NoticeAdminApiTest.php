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

/**
 * @internal
 * @coversNothing
 */
final class NoticeAdminApiTest extends ProfileRecordAdminCase
{
    use NoticeAdminApiFixture;

    public function testPageDetailCreatePublishWithdrawAndReceiptsContracts(): void
    {
        $this->grantPermissions('education:academic:notice:page', 'education:academic:notice:detail', 'education:academic:notice:create', 'education:academic:notice:publish', 'education:academic:notice:withdraw', 'education:academic:notice:receipt');
        $fixture = $this->noticeFixture('notice_admin_api');
        $headers = $this->tenantHeaders($fixture['tenant']);

        $created = $this->post('/admin/education/academic/notices', $this->noticePayload($fixture), $headers);
        $page = $this->get('/admin/education/academic/notices/page', ['page' => 1, 'pageSize' => 20], $headers);
        $detail = $this->get('/admin/education/academic/notices/' . $created['data']['id'], [], $headers);
        $published = $this->put('/admin/education/academic/notices/' . $created['data']['id'] . '/publish', ['published_at' => '2026-06-12 09:00:00'], $headers);
        $receipts = $this->get('/admin/education/academic/notices/' . $created['data']['id'] . '/receipts/page', ['page' => 1, 'pageSize' => 20], $headers);
        $withdrawn = $this->put('/admin/education/academic/notices/' . $created['data']['id'] . '/withdraw', ['withdraw_reason' => 'Wrong class target'], $headers);

        self::assertSame(ResultCode::SUCCESS->value, $created['code']);
        self::assertSame('draft', $created['data']['status']);
        self::assertSame(ResultCode::SUCCESS->value, $page['code']);
        self::assertSame($created['data']['id'], $page['data']['list'][0]['id']);
        self::assertSame(ResultCode::SUCCESS->value, $detail['code']);
        self::assertSame('Class reminder', $detail['data']['title']);
        self::assertSame(1, $published['data']['receipt_count']);
        self::assertSame(1, $receipts['data']['total']);
        self::assertSame('withdrawn', $withdrawn['data']['status']);
        self::assertSame('Wrong class target', $withdrawn['data']['withdraw_reason']);
    }
}
