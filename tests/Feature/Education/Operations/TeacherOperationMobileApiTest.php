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
use App\Model\Education\Operations\EducationLessonChangeRequest;
use App\Model\Education\Operations\EducationTeacherWorkloadRecord;

/**
 * @internal
 * @coversNothing
 */
final class TeacherOperationMobileApiTest extends OperationApiCase
{
    public function testTeacherCanReadOnlyAssignedChangedLessons(): void
    {
        $fixture = $this->fixture('ops_teacher_mobile');
        $this->createMobileProfile($fixture['tenant'], $fixture['campus'], 'teacher');
        EducationLessonChangeRequest::query()->create(['tenant_id' => $fixture['tenant']->id, 'campus_id' => $fixture['campus']->id, 'lesson_id' => $fixture['lesson']->id, 'change_type' => 'reschedule', 'status' => 'pending', 'old_values_json' => [], 'new_values_json' => [], 'reason' => 'training', 'requested_by' => $this->user->id]);

        $result = $this->get('/mobile/education/operations/teacher/changed-lessons', [], $this->mobileHeaders($fixture['tenant']));

        self::assertSame(ResultCode::SUCCESS->value, $result['code']);
        self::assertSame(1, \count($result['data']['list']));
    }

    public function testTeacherWorkloadSummaryDoesNotAcceptOverride(): void
    {
        $fixture = $this->fixture('ops_teacher_workload_mobile');
        $this->createMobileProfile($fixture['tenant'], $fixture['campus'], 'teacher');
        EducationTeacherWorkloadRecord::query()->create(['tenant_id' => $fixture['tenant']->id, 'campus_id' => $fixture['campus']->id, 'teacher_id' => $this->user->id, 'lesson_id' => $fixture['lesson']->id, 'workload_type' => 'main', 'lesson_type' => 'normal', 'credits' => '1.00', 'student_count' => 1, 'present_count' => 1, 'leave_count' => 0, 'absent_count' => 0, 'recorded_at' => '2026-06-10 11:00:00']);

        $result = $this->get('/mobile/education/operations/teacher/workload-summary', ['teacher_id' => $this->user->id + 99], $this->mobileHeaders($fixture['tenant']));

        self::assertSame(ResultCode::SUCCESS->value, $result['code']);
        self::assertSame(1, $result['data']['row_count']);
    }
}
