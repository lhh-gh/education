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

namespace App\Service\Education\Content;

use App\Repository\Education\Content\StudentWorkRepository;
use App\Service\Education\Foundation\EducationUserContext;

final class StudentWorkService
{
    public function __construct(private readonly StudentWorkRepository $works) {}

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(array $filters, EducationUserContext $context, int $page = 1, int $pageSize = 20): array
    {
        return $this->works->page($filters, $context, $page, $pageSize);
    }

    /**
     * @param array<string, mixed> $data
     * @param list<int> $assignedStudentIds
     * @return array{student_work_id: int, status: string}
     */
    public function saveForTeacher(array $data, array $assignedStudentIds): array
    {
        if (! \in_array((int) $data['student_id'], $assignedStudentIds, true)) {
            throw new \RuntimeException('student is not assigned to current teacher', 403);
        }
        unset($data['attachment_ids']);
        $work = $this->works->save($data + ['status' => 'draft']);

        return ['student_work_id' => (int) $work->id, 'status' => $this->statusValue($work->status)];
    }

    /**
     * @return array{student_work_id: int, status: string}
     */
    public function publish(EducationUserContext $context, int $workId): array
    {
        $work = $this->works->findInContext($context, $workId);
        $work->status = 'published';
        $work->published_at = date('Y-m-d H:i:s');
        $work->save();

        return ['student_work_id' => $workId, 'status' => 'published'];
    }

    /**
     * @return array{student_work_id: int, status: string}
     */
    public function withdraw(EducationUserContext $context, int $workId): array
    {
        $work = $this->works->findInContext($context, $workId);
        $work->status = 'withdrawn';
        $work->save();

        return ['student_work_id' => $workId, 'status' => 'withdrawn'];
    }

    private function statusValue(mixed $status): string
    {
        return $status instanceof \BackedEnum ? (string) $status->value : (string) $status;
    }
}
