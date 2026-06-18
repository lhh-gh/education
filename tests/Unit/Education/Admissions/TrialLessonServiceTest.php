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

namespace HyperfTests\Unit\Education\Admissions;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Admissions\EducationLeadStudent;
use App\Service\Education\Admissions\TrialLessonService;

/**
 * @internal
 * @coversNothing
 */
final class TrialLessonServiceTest extends AdmissionsTestCase
{
    public function testTrialRejectsTeacherAndClassroomConflict(): void
    {
        $tenant = $this->tenant('adm_trial');
        $campus = $this->campus($tenant);
        $lead = $this->leadFixture($tenant, $campus);
        $student = EducationLeadStudent::query()->where('tenant_id', $tenant->id)->where('lead_id', $lead->id)->first();
        $context = $this->context((int) $tenant->id, campusIds: [(int) $campus->id]);
        $service = make(TrialLessonService::class);

        $first = $service->create([
            'lead_id' => (int) $lead->id,
            'lead_student_id' => (int) $student->id,
            'course_id' => 501,
            'teacher_id' => 701,
            'classroom_id' => 801,
            'start_time' => '2026-06-13 09:00:00',
            'end_time' => '2026-06-13 10:00:00',
        ], $context);

        try {
            $service->create([
                'lead_id' => (int) $lead->id,
                'lead_student_id' => (int) $student->id,
                'course_id' => 501,
                'teacher_id' => 701,
                'classroom_id' => 802,
                'start_time' => '2026-06-13 09:30:00',
                'end_time' => '2026-06-13 10:30:00',
            ], $context);
            self::fail('Expected teacher conflict.');
        } catch (BusinessException $exception) {
            self::assertSame(ResultCode::CONFLICT, $exception->getResponse()->code);
            self::assertSame((int) $first['id'], $exception->getResponse()->data['conflict_lesson_id']);
        }
    }
}
