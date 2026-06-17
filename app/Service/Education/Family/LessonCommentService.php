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
use App\Model\Education\Family\EducationLessonComment;
use App\Repository\Education\Family\LessonCommentRepository;
use App\Service\Education\Academic\TeacherMobileContextResolver;
use App\Service\Education\Foundation\EducationUserContext;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;

final class LessonCommentService
{
    public function __construct(
        private readonly TeacherMobileContextResolver $teacherResolver,
        private readonly LessonCommentRepository $repository,
        private readonly ServiceQualityService $qualityService
    ) {}

    /**
     * @param array<string, mixed> $data
     * @return array{lesson_comment_id: int, status: string}
     */
    public function saveTeacherComment(array $data, EducationUserContext $context): array
    {
        $teacher = $this->teacherResolver->resolveTeacher($context);
        $tenantId = (int) $context->tenantId;
        $lessonId = (int) ($data['lesson_id'] ?? 0);
        $studentId = (int) ($data['student_id'] ?? 0);
        if (! $this->repository->isTeacherAssigned($tenantId, $lessonId, $studentId, (int) $teacher->id)) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'teacher is not assigned to this lesson student', ['lesson_id' => $lessonId, 'student_id' => $studentId]);
        }

        return Db::transaction(function () use ($data, $context, $teacher, $tenantId, $lessonId, $studentId): array {
            $publish = (bool) ($data['publish'] ?? false);
            $comment = $this->repository->createComment([
                'tenant_id' => $tenantId,
                'campus_id' => $context->currentCampusId,
                'lesson_id' => $lessonId,
                'student_id' => $studentId,
                'teacher_id' => (int) $teacher->id,
                'content' => (string) ($data['content'] ?? ''),
                'status' => $publish ? 'published' : 'draft',
                'published_at' => $publish ? Carbon::now() : null,
                'created_by' => $context->userId,
                'updated_by' => $context->userId,
            ]);
            $this->repository->syncTags($comment, array_map('intval', (array) ($data['tag_ids'] ?? [])));
            if ($publish) {
                $this->qualityService->incrementComment($tenantId, $context->currentCampusId, (int) $teacher->id, $studentId);
            }

            return ['lesson_comment_id' => (int) $comment->id, 'status' => $this->statusValue($comment)];
        });
    }

    private function statusValue(EducationLessonComment $comment): string
    {
        return $comment->status instanceof \BackedEnum ? (string) $comment->status->value : (string) $comment->status;
    }
}
