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

namespace HyperfTests\Unit\Education\Family;

use App\Exception\BusinessException;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Family\LessonCommentService;

/**
 * @internal
 * @coversNothing
 */
final class LessonCommentServiceTest extends FamilyTestCase
{
    public function testTeacherCanCommentAssignedLessonStudentOnly(): void
    {
        $fixture = $this->familyFixture('family_comment');
        $service = make(LessonCommentService::class);
        $context = $this->context($fixture['tenant_id'], EducationRoleCode::Teacher, [$fixture['campus_id']], $fixture['teacher_user_id']);

        $created = $service->saveTeacherComment([
            'lesson_id' => $fixture['lesson_id'],
            'student_id' => $fixture['student_id'],
            'content' => 'Participated actively.',
            'publish' => true,
        ], $context);

        self::assertSame('published', $created['status']);

        $this->expectException(BusinessException::class);
        $this->expectExceptionCode(403);

        $service->saveTeacherComment([
            'lesson_id' => $fixture['lesson_id'],
            'student_id' => $fixture['other_student_id'],
            'content' => 'Should be blocked.',
            'publish' => true,
        ], $context);
    }
}
