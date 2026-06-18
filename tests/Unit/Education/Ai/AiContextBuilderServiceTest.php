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

namespace HyperfTests\Unit\Education\Ai;

use App\Exception\BusinessException;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Ai\AiContextBuilderService;

/**
 * @internal
 * @coversNothing
 */
final class AiContextBuilderServiceTest extends AiTestCase
{
    public function testTeacherContextExcludesUnassignedStudents(): void
    {
        $fixture = $this->aiFixture('ai_context');
        $service = make(AiContextBuilderService::class);
        $context = $this->context($fixture['tenant_id'], EducationRoleCode::Teacher, [$fixture['campus_id']], $fixture['teacher_user_id']);

        $built = $service->teacherLessonStudentContext($fixture['lesson_id'], $fixture['student_id'], $context);

        self::assertSame($fixture['student_id'], $built['student_id']);
        self::assertSame($fixture['lesson_id'], $built['lesson_id']);
        self::assertNotEmpty($built['context_hash']);

        $this->expectException(BusinessException::class);
        $this->expectExceptionCode(403);
        $service->teacherLessonStudentContext($fixture['lesson_id'], $fixture['other_student_id'], $context);
    }
}
