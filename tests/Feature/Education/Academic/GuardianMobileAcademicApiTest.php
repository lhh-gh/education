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
final class GuardianMobileAcademicApiTest extends ProfileRecordAdminCase
{
    use GuardianMobileApiFixture;

    public function testLessonsAccountsAndConsumptionsContracts(): void
    {
        $fixture = $this->guardianFixture('guardian_mobile_academic_api');
        $headers = $this->mobileHeaders($fixture['tenant']);

        $lessons = $this->get('/mobile/education/academic/guardian/students/' . $fixture['student']->id . '/lessons', ['start_at' => '2026-06-01 00:00:00', 'end_at' => '2026-06-30 23:59:59'], $headers);
        $accounts = $this->get('/mobile/education/academic/guardian/students/' . $fixture['student']->id . '/accounts', ['status' => 'active'], $headers);
        $consumptions = $this->get('/mobile/education/academic/guardian/students/' . $fixture['student']->id . '/consumptions', ['account_id' => $fixture['account']->id, 'source_type' => 'attendance'], $headers);

        self::assertSame(ResultCode::SUCCESS->value, $lessons['code']);
        self::assertSame((int) $fixture['lesson_student']->id, $lessons['data']['list'][0]['lesson_student_id']);
        self::assertSame(ResultCode::SUCCESS->value, $accounts['code']);
        self::assertSame((int) $fixture['account']->id, $accounts['data']['list'][0]['id']);
        self::assertSame(ResultCode::SUCCESS->value, $consumptions['code']);
        self::assertSame((int) $fixture['consumption']->id, $consumptions['data']['list'][0]['id']);
    }
}
