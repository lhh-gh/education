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

namespace HyperfTests\Feature\Education\Operations;

use App\Http\Common\ResultCode;
use App\Model\Education\Operations\EducationMakeupEntitlement;
use App\Model\Education\Operations\EducationRenewalAlert;

/**
 * @internal
 * @coversNothing
 */
final class GuardianOperationMobileApiTest extends OperationApiCase
{
    public function testGuardianReadsOnlyBoundStudentEntitlements(): void
    {
        $fixture = $this->fixture('ops_guardian_mobile');
        $this->createMobileProfile($fixture['tenant'], $fixture['campus'], 'guardian');
        $student = $this->boundStudent($fixture['tenant'], $fixture['campus']);
        EducationMakeupEntitlement::query()->create(['tenant_id' => $fixture['tenant']->id, 'campus_id' => $fixture['campus']->id, 'student_id' => $student->id, 'course_id' => $fixture['course']->id, 'source_lesson_id' => $fixture['lesson']->id, 'source_leave_request_id' => 1, 'status' => 'available']);

        $bound = $this->get('/mobile/education/operations/guardian/makeup-entitlements', ['student_id' => $student->id], $this->mobileHeaders($fixture['tenant']));
        $unbound = $this->get('/mobile/education/operations/guardian/makeup-entitlements', ['student_id' => $student->id + 99], $this->mobileHeaders($fixture['tenant']));

        self::assertSame(ResultCode::SUCCESS->value, $bound['code']);
        self::assertSame(ResultCode::FORBIDDEN->value, $unbound['code']);
    }

    public function testGuardianRenewalAlertsAreScopedToBoundStudent(): void
    {
        $fixture = $this->fixture('ops_guardian_renewal_mobile');
        $this->createMobileProfile($fixture['tenant'], $fixture['campus'], 'guardian');
        $student = $this->boundStudent($fixture['tenant'], $fixture['campus']);
        EducationRenewalAlert::query()->create(['tenant_id' => $fixture['tenant']->id, 'campus_id' => $fixture['campus']->id, 'student_id' => $student->id, 'course_id' => $fixture['course']->id, 'student_course_account_id' => 1, 'alert_type' => 'low_balance', 'alert_level' => 'urgent', 'status' => 'open', 'trigger_value' => '1.00', 'threshold_value' => '2.00']);

        $result = $this->get('/mobile/education/operations/guardian/renewal-alerts', ['student_id' => $student->id], $this->mobileHeaders($fixture['tenant']));

        self::assertSame(ResultCode::SUCCESS->value, $result['code']);
        self::assertSame(1, \count($result['data']['list']));
    }
}
