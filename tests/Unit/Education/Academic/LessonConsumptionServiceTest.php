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

namespace HyperfTests\Unit\Education\Academic;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Academic\EducationLessonAttendance;
use App\Model\Education\Academic\EducationLessonConsumption;
use App\Model\Education\Academic\EducationStudentCourseAccount;
use App\Service\Education\Academic\LessonConsumptionService;

/**
 * @internal
 * @coversNothing
 */
final class LessonConsumptionServiceTest extends AcademicTestCase
{
    public function testRollbackConsumptionCreatesReversalAndRestoresAccount(): void
    {
        [$tenantId, $campusId, $account, $attendance, $consumption] = $this->fixture();

        $result = make(LessonConsumptionService::class)->rollback((int) $consumption->id, 'wrong attendance', $this->context($tenantId, campusIds: [$campusId]), 901);

        self::assertSame('reversed', $result['original']['status']);
        self::assertSame('rollback', $result['rollback']['source_type']);
        self::assertSame('10.00', $account->refresh()->available_units);
        self::assertSame('0.00', $account->refresh()->consumed_units);
        self::assertSame('reversed', $attendance->refresh()->consumption_status);
    }

    public function testRollbackConsumptionIsRejectedWhenAlreadyReversed(): void
    {
        [$tenantId, $campusId, , , $consumption] = $this->fixture(status: 'reversed');

        try {
            make(LessonConsumptionService::class)->rollback((int) $consumption->id, 'again', $this->context($tenantId, campusIds: [$campusId]), 901);
            self::fail('Expected reversed consumption to fail.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::CONFLICT, $exception->getResponse()->code);
        }
    }

    private function fixture(string $status = 'active'): array
    {
        $tenant = $this->tenant('tenant');
        $campus = $this->campus($tenant, 'main');
        $account = EducationStudentCourseAccount::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'student_id' => 201,
            'course_id' => 301,
            'purchased_units' => '10.00',
            'consumed_units' => '1.00',
            'adjusted_units' => '0.00',
            'available_units' => '9.00',
            'status' => 'active',
        ]);
        $attendance = EducationLessonAttendance::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'lesson_id' => 401,
            'lesson_student_id' => 501,
            'class_id' => 601,
            'course_id' => 301,
            'student_id' => 201,
            'account_id' => $account->id,
            'attendance_status' => 'present',
            'consume_policy' => 'consume',
            'planned_units' => '1.00',
            'consumed_units' => '1.00',
            'consumption_status' => 'active',
            'attendance_batch_no' => 'ATT001',
        ]);
        $consumption = EducationLessonConsumption::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'consumption_no' => 'CON-001',
            'account_id' => $account->id,
            'student_id' => 201,
            'course_id' => 301,
            'lesson_id' => 401,
            'lesson_student_id' => 501,
            'attendance_id' => $attendance->id,
            'source_type' => 'attendance',
            'direction' => 'decrease',
            'units' => '1.00',
            'before_available_units' => '10.00',
            'after_available_units' => '9.00',
            'before_consumed_units' => '0.00',
            'after_consumed_units' => '1.00',
            'status' => $status,
            'reason' => 'attendance',
        ]);

        return [(int) $tenant->id, (int) $campus->id, $account, $attendance, $consumption];
    }
}
