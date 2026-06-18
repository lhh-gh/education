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
final class GuardianMobileNoticeApiTest extends ProfileRecordAdminCase
{
    use GuardianMobileApiFixture;

    public function testNoticePageDetailAndReadContracts(): void
    {
        $fixture = $this->guardianFixture('guardian_mobile_notice_api');
        $receipt = $this->noticeReceipt($fixture);
        $headers = $this->mobileHeaders($fixture['tenant']);

        $page = $this->get('/mobile/education/academic/guardian/notices/page', ['status' => 'unread'], $headers);
        $detail = $this->get('/mobile/education/academic/guardian/notices/' . $receipt->id, [], $headers);
        $read = $this->put('/mobile/education/academic/guardian/notices/' . $receipt->id . '/read', [], $headers);

        self::assertSame(ResultCode::SUCCESS->value, $page['code']);
        self::assertSame((int) $receipt->id, $page['data']['list'][0]['receipt_id']);
        self::assertSame(ResultCode::SUCCESS->value, $detail['code']);
        self::assertSame('Class reminder', $detail['data']['title']);
        self::assertSame(ResultCode::SUCCESS->value, $read['code']);
        self::assertSame('read', $read['data']['status']);
        self::assertSame(1, (int) $receipt->notice->refresh()->read_count);
    }
}
