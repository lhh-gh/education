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

namespace HyperfTests\Feature\Education\Growth;

use App\Http\Common\ResultCode;
use App\Model\Education\Admissions\EducationTrialLesson;

/**
 * @internal
 * @coversNothing
 */
final class TeacherGrowthMobileApiTest extends GrowthApiCase
{
    public function testTeacherCanSubmitAssignedTrialFeedbackOnly(): void
    {
        $fixture = $this->growthFixture('growth_teacher_mobile', 'teacher');
        $headers = $this->mobileHeaders($fixture['tenant']);
        $otherTrial = EducationTrialLesson::query()->create([
            'tenant_id' => $fixture['tenant_id'],
            'campus_id' => $fixture['campus_id'],
            'lead_id' => $fixture['lead_id'],
            'lead_student_id' => 1,
            'course_id' => $fixture['course_id'],
            'teacher_id' => $this->user->id + 1000,
            'start_time' => '2026-06-10 11:00:00',
            'end_time' => '2026-06-10 12:00:00',
            'status' => 'scheduled',
            'consultant_user_id' => $this->user->id,
        ]);

        $ok = $this->post('/mobile/education/growth/teacher/trial-feedback', [
            'trial_lesson_id' => $fixture['trial_lesson_id'],
            'classroom_performance' => 'focused',
            'course_recommendation' => 'beginner art',
            'teacher_note' => 'good fit',
        ], $headers);
        self::assertSame(ResultCode::SUCCESS->value, $ok['code']);
        self::assertArrayHasKey('trial_feedback_id', $ok['data']);

        $denied = $this->post('/mobile/education/growth/teacher/trial-feedback', [
            'trial_lesson_id' => $otherTrial->id,
            'classroom_performance' => 'focused',
            'course_recommendation' => 'beginner art',
            'teacher_note' => 'good fit',
        ], $headers);
        self::assertSame(ResultCode::FORBIDDEN->value, $denied['code']);
    }
}
