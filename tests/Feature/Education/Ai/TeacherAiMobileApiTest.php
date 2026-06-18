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

namespace HyperfTests\Feature\Education\Ai;

use App\Http\Common\ResultCode;

/**
 * @internal
 * @coversNothing
 */
final class TeacherAiMobileApiTest extends AiApiCase
{
    public function testTeacherCanRequestDraftOnlyForAssignedLessonStudent(): void
    {
        $fixture = $this->aiFixture('ai_teacher_api', 'teacher');
        $headers = $this->mobileHeaders($fixture['tenant']);

        $assigned = $this->post('/mobile/education/ai/teacher/lesson-comment-drafts', [
            'lesson_id' => $fixture['lesson_id'],
            'student_id' => $fixture['student_id'],
            'keywords' => ['active', 'needs practice'],
        ], $headers);
        self::assertSame(ResultCode::SUCCESS->value, $assigned['code']);
        self::assertSame('queued', $assigned['data']['status']);

        $unassigned = $this->post('/mobile/education/ai/teacher/lesson-comment-drafts', [
            'lesson_id' => $fixture['lesson_id'],
            'student_id' => $fixture['other_student_id'],
            'keywords' => ['active'],
        ], $headers);
        self::assertSame(ResultCode::FORBIDDEN->value, $unassigned['code']);
        self::assertSame('teacher is not assigned to this lesson student', $unassigned['message']);
    }
}
