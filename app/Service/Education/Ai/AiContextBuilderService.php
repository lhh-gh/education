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

namespace App\Service\Education\Ai;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Academic\EducationLessonStudent;
use App\Service\Education\Academic\TeacherMobileContextResolver;
use App\Service\Education\Foundation\EducationUserContext;

final class AiContextBuilderService
{
    public function __construct(private readonly TeacherMobileContextResolver $teacherResolver) {}

    /**
     * @return array{lesson_id: int, student_id: int, teacher_id: int, context_hash: string}
     */
    public function teacherLessonStudentContext(int $lessonId, int $studentId, EducationUserContext $context): array
    {
        $teacher = $this->teacherResolver->resolveTeacher($context);
        $allowed = EducationLessonStudent::query()
            ->join('edu_lessons', 'edu_lessons.id', '=', 'edu_lesson_students.lesson_id')
            ->where('edu_lesson_students.tenant_id', (int) $context->tenantId)
            ->where('edu_lesson_students.lesson_id', $lessonId)
            ->where('edu_lesson_students.student_id', $studentId)
            ->where('edu_lessons.teacher_id', (int) $teacher->id)
            ->exists();

        if (! $allowed) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'teacher is not assigned to this lesson student', ['lesson_id' => $lessonId, 'student_id' => $studentId]);
        }

        return [
            'lesson_id' => $lessonId,
            'student_id' => $studentId,
            'teacher_id' => (int) $teacher->id,
            'context_hash' => hash('sha256', implode(':', [(int) $context->tenantId, $lessonId, $studentId, (int) $teacher->id])),
        ];
    }
}
