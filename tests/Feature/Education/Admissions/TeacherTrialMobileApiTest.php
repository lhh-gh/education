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

namespace HyperfTests\Feature\Education\Admissions;

use App\Http\Common\ResultCode;
use App\Model\Education\Admissions\EducationLeadStudent;
use App\Model\Education\Admissions\EducationTrialLesson;

/**
 * @internal
 * @coversNothing
 */
final class TeacherTrialMobileApiTest extends AdmissionsApiCase
{
    public function testTeacherCanSubmitOnlyAssignedTrialFeedback(): void
    {
        $tenant = $this->tenant('adm_mobile_teacher');
        $campus = $this->campus($tenant);
        $this->createTenantProfile($tenant, 'teacher', $campus);
        $lead = $this->admissionsLead($tenant, $campus);
        $student = EducationLeadStudent::query()->where('tenant_id', $tenant->id)->where('lead_id', $lead->id)->first();
        $assigned = EducationTrialLesson::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'lead_id' => $lead->id,
            'lead_student_id' => $student->id,
            'course_id' => 501,
            'teacher_id' => $this->user->id,
            'start_time' => '2026-06-13 09:00:00',
            'end_time' => '2026-06-13 10:00:00',
            'status' => 'scheduled',
        ]);
        $unassigned = EducationTrialLesson::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'lead_id' => $lead->id,
            'lead_student_id' => $student->id,
            'course_id' => 501,
            'teacher_id' => 999999,
            'start_time' => '2026-06-13 11:00:00',
            'end_time' => '2026-06-13 12:00:00',
            'status' => 'scheduled',
        ]);

        $list = $this->get('/mobile/education/admissions/teacher/trial-lessons', ['date' => '2026-06-13'], $this->mobileHeaders($tenant));
        $feedback = $this->post('/mobile/education/admissions/teacher/trial-feedbacks', ['trial_lesson_id' => $assigned->id, 'score' => 4, 'content' => 'Good'], $this->mobileHeaders($tenant));
        $forbidden = $this->post('/mobile/education/admissions/teacher/trial-feedbacks', ['trial_lesson_id' => $unassigned->id, 'score' => 4, 'content' => 'No'], $this->mobileHeaders($tenant));

        self::assertSame(ResultCode::SUCCESS->value, $list['code']);
        self::assertCount(1, $list['data']['list']);
        self::assertSame(ResultCode::SUCCESS->value, $feedback['code']);
        self::assertSame(ResultCode::FORBIDDEN->value, $forbidden['code']);
    }
}
