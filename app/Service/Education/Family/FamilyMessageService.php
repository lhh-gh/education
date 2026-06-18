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

namespace App\Service\Education\Family;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Education\Academic\EducationLessonStudent;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Repository\Education\Family\FamilyMessageRepository;
use App\Repository\Education\Family\HomeworkRepository;
use App\Service\Education\Academic\GuardianMobileContextResolver;
use App\Service\Education\Academic\TeacherMobileContextResolver;
use App\Service\Education\Foundation\EducationUserContext;

final class FamilyMessageService
{
    public function __construct(
        private readonly FamilyMessageRepository $repository,
        private readonly HomeworkRepository $homeworkRepository,
        private readonly TeacherMobileContextResolver $teacherResolver,
        private readonly GuardianMobileContextResolver $guardianResolver
    ) {}

    /**
     * @param array<string, mixed> $data
     * @return array{message_id: int, status: string}
     */
    public function send(array $data, EducationUserContext $context): array
    {
        $studentId = (int) ($data['student_id'] ?? 0);
        $senderType = $this->assertCanMessage($studentId, $context);
        $message = $this->repository->create([
            'tenant_id' => (int) $context->tenantId,
            'campus_id' => $context->currentCampusId,
            'thread_id' => (string) ($data['thread_id'] ?? ('student-' . $studentId)),
            'student_id' => $studentId,
            'sender_type' => $senderType,
            'sender_user_id' => $context->userId,
            'receiver_user_id' => isset($data['receiver_user_id']) ? (int) $data['receiver_user_id'] : null,
            'content' => (string) ($data['content'] ?? ''),
            'status' => 'sent',
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ]);

        return ['message_id' => (int) $message->id, 'status' => 'sent'];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function thread(int $studentId, string $threadId, EducationUserContext $context): array
    {
        $this->assertCanMessage($studentId, $context);

        return $this->repository->thread((int) $context->tenantId, $threadId, $studentId);
    }

    private function assertCanMessage(int $studentId, EducationUserContext $context): string
    {
        if ($context->roleCode === EducationRoleCode::Guardian) {
            $guardian = $this->guardianResolver->resolveGuardian($context);
            if (! $this->homeworkRepository->isGuardianBound((int) $context->tenantId, $studentId, (int) $guardian->id)) {
                throw new BusinessException(ResultCode::FORBIDDEN, 'message thread is outside current user scope', ['student_id' => $studentId]);
            }

            return 'guardian';
        }

        if ($context->roleCode === EducationRoleCode::Teacher) {
            $teacher = $this->teacherResolver->resolveTeacher($context);
            $allowed = EducationLessonStudent::query()
                ->join('edu_lessons', 'edu_lessons.id', '=', 'edu_lesson_students.lesson_id')
                ->where('edu_lesson_students.tenant_id', (int) $context->tenantId)
                ->where('edu_lesson_students.student_id', $studentId)
                ->where('edu_lessons.teacher_id', (int) $teacher->id)
                ->exists();
            if (! $allowed) {
                throw new BusinessException(ResultCode::FORBIDDEN, 'message thread is outside current user scope', ['student_id' => $studentId]);
            }

            return 'teacher';
        }

        return 'admin';
    }
}
