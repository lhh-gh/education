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

namespace HyperfTests\Feature\Education\Family;

use App\Http\Common\ResultCode;

/**
 * @internal
 * @coversNothing
 */
final class TeacherFamilyMobileApiTest extends FamilyApiCase
{
    public function testTeacherLessonCommentApiEnforcesAssignedStudent(): void
    {
        $fixture = $this->familyFixture('teacher_family_api', 'teacher');
        $headers = $this->mobileHeaders($fixture['tenant']);

        $created = $this->post('/mobile/education/family/teacher/lesson-comments', [
            'lesson_id' => $fixture['lesson_id'],
            'student_id' => $fixture['student_id'],
            'content' => 'Participated actively.',
            'publish' => true,
        ], $headers);

        self::assertSame(ResultCode::SUCCESS->value, $created['code']);
        self::assertSame('published', $created['data']['status']);

        $blocked = $this->post('/mobile/education/family/teacher/lesson-comments', [
            'lesson_id' => $fixture['lesson_id'],
            'student_id' => $fixture['other_student_id'],
            'content' => 'Blocked.',
            'publish' => true,
        ], $headers);

        self::assertSame(ResultCode::FORBIDDEN->value, $blocked['code']);
    }
}
