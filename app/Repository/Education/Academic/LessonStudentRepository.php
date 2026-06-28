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

namespace App\Repository\Education\Academic;

use App\Model\Education\Academic\EducationLessonStudent;
use App\Repository\IRepository;
use App\Service\Education\Foundation\EducationScopeQuery;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\Database\Model\Builder;
use Hyperf\DbConnection\Db;

/**
 * @extends IRepository<EducationLessonStudent>
 */
final class LessonStudentRepository extends IRepository
{
    public function __construct(
        protected readonly EducationLessonStudent $model
    ) {}

    public function listByLesson(int $lessonId, EducationUserContext $context): array
    {
        return $this->applyContext($this->getQuery(), $context)
            ->where('lesson_id', $lessonId)
            ->orderBy('id')
            ->get()
            ->toArray();
    }

    public function bulkCreateSnapshots(int $lessonId, array $rows): array
    {
        $created = [];
        foreach ($rows as $row) {
            $row['lesson_id'] = $lessonId;
            $created[] = $this->create($row)->refresh()->toArray();
        }

        return $created;
    }

    public function replacePlannedSnapshots(int $lessonId, array $rows, ?int $operatorId): array
    {
        return Db::transaction(function () use ($lessonId, $rows, $operatorId): array {
            $existing = $this->getQuery()->where('lesson_id', $lessonId)->get()->keyBy('student_id');
            $incomingIds = [];
            $saved = [];
            foreach ($rows as $row) {
                $studentId = (int) $row['student_id'];
                $incomingIds[] = $studentId;
                $row['lesson_id'] = $lessonId;
                $row['status'] = 'planned';
                $row['updated_by'] = $operatorId;
                $current = $existing->get($studentId);
                if ($current instanceof EducationLessonStudent) {
                    $current->fill($row);
                    $current->save();
                    $saved[] = $current->refresh()->toArray();
                    continue;
                }
                $row['created_by'] = $operatorId;
                $saved[] = $this->create($row)->refresh()->toArray();
            }

            $this->getQuery()
                ->where('lesson_id', $lessonId)
                ->where('status', 'planned')
                ->when($incomingIds !== [], static fn (Builder $query) => $query->whereNotIn('student_id', $incomingIds))
                ->update(['status' => 'cancelled', 'updated_by' => $operatorId]);

            return $saved;
        });
    }

    public function cancelByLesson(int $lessonId, ?int $operatorId): int
    {
        return $this->getQuery()
            ->where('lesson_id', $lessonId)
            ->where('status', 'planned')
            ->update([
                'status' => 'cancelled',
                'updated_by' => $operatorId,
            ]);
    }

    public function overlappingStudentLessons(array $studentIds, string $startAt, string $endAt, int $tenantId, int $campusId, ?int $excludeLessonId = null): array
    {
        if ($studentIds === []) {
            return [];
        }

        return Db::table('edu_lesson_students as ls')
            ->join('edu_lessons as l', 'l.id', '=', 'ls.lesson_id')
            ->where('ls.tenant_id', $tenantId)
            ->where('ls.campus_id', $campusId)
            ->whereIn('ls.student_id', $studentIds)
            ->where('ls.status', 'planned')
            ->whereNull('ls.deleted_at')
            ->where('l.status', '<>', 'cancelled')
            ->whereNull('l.deleted_at')
            ->where('l.start_at', '<', $endAt)
            ->where('l.end_at', '>', $startAt)
            ->when($excludeLessonId !== null, static fn ($query) => $query->where('l.id', '<>', $excludeLessonId))
            ->orderBy('l.start_at')
            ->get(['ls.*', 'l.start_at', 'l.end_at'])
            ->map(static fn (object $row): array => (array) $row)
            ->toArray();
    }

    private function applyContext(Builder $query, EducationUserContext $context): Builder
    {
        (new EducationScopeQuery())->applyTenantCampus($query, [], $context);

        return $query;
    }
}
