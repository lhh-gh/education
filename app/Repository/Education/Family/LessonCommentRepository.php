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

namespace App\Repository\Education\Family;

use App\Model\Education\Academic\EducationLesson;
use App\Model\Education\Academic\EducationLessonStudent;
use App\Model\Education\Family\EducationLessonComment;
use App\Model\Education\Family\EducationLessonCommentTagRelation;
use App\Model\Education\Family\EducationLessonCommentTemplate;
use App\Model\Education\Family\EducationStudentPerformanceTag;
use App\Service\Education\Foundation\EducationUserContext;

final class LessonCommentRepository
{
    public function isTeacherAssigned(int $tenantId, int $lessonId, int $studentId, int $teacherId): bool
    {
        $lessonExists = EducationLesson::query()
            ->where('tenant_id', $tenantId)
            ->where('id', $lessonId)
            ->where('teacher_id', $teacherId)
            ->exists();

        if (! $lessonExists) {
            return false;
        }

        return EducationLessonStudent::query()
            ->where('tenant_id', $tenantId)
            ->where('lesson_id', $lessonId)
            ->where('student_id', $studentId)
            ->exists();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createComment(array $data): EducationLessonComment
    {
        return EducationLessonComment::query()->create($data);
    }

    /**
     * @param int[] $tagIds
     */
    public function syncTags(EducationLessonComment $comment, array $tagIds): void
    {
        EducationLessonCommentTagRelation::query()
            ->where('tenant_id', (int) $comment->tenant_id)
            ->where('lesson_comment_id', (int) $comment->id)
            ->delete();

        foreach (array_values(array_unique($tagIds)) as $tagId) {
            EducationLessonCommentTagRelation::query()->create([
                'tenant_id' => (int) $comment->tenant_id,
                'campus_id' => $comment->campus_id,
                'lesson_comment_id' => (int) $comment->id,
                'performance_tag_id' => (int) $tagId,
                'student_id' => (int) $comment->student_id,
                'created_by' => $comment->created_by,
                'updated_by' => $comment->updated_by,
            ]);
        }
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function pageTemplates(array $filters, EducationUserContext $context): array
    {
        $query = EducationLessonCommentTemplate::query()->where('tenant_id', $context->tenantId);
        if (($filters['course_id'] ?? '') !== '') {
            $query->where('course_id', (int) $filters['course_id']);
        }
        if (($filters['status'] ?? '') !== '') {
            $query->where('status', (string) $filters['status']);
        }

        return $this->paginate($query, $filters);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function saveTemplate(array $data): EducationLessonCommentTemplate
    {
        if (isset($data['id']) && $data['id'] !== '') {
            $template = EducationLessonCommentTemplate::query()->findOrFail((int) $data['id']);
            $template->fill($data);
            $template->save();

            return $template;
        }

        return EducationLessonCommentTemplate::query()->create($data);
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function pageTags(array $filters, EducationUserContext $context): array
    {
        $query = EducationStudentPerformanceTag::query()->where('tenant_id', $context->tenantId);
        if (($filters['tag_type'] ?? '') !== '') {
            $query->where('tag_type', (string) $filters['tag_type']);
        }
        if (($filters['status'] ?? '') !== '') {
            $query->where('status', (string) $filters['status']);
        }

        return $this->paginate($query, $filters);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function saveTag(array $data): EducationStudentPerformanceTag
    {
        if (isset($data['id']) && $data['id'] !== '') {
            $tag = EducationStudentPerformanceTag::query()->findOrFail((int) $data['id']);
            $tag->fill($data);
            $tag->save();

            return $tag;
        }

        return EducationStudentPerformanceTag::query()->create($data);
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    private function paginate(mixed $query, array $filters): array
    {
        $total = (clone $query)->count();
        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($filters['pageSize'] ?? 20)));
        $list = $query->orderBy('sort_order')->orderByDesc('id')->forPage($page, $pageSize)->get()
            ->map(static fn (mixed $row): array => $row->toArray())
            ->all();

        return ['list' => $list, 'total' => $total];
    }
}
