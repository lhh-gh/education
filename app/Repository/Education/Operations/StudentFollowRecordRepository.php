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

namespace App\Repository\Education\Operations;

use App\Model\Education\Operations\EducationStudentFollowRecord;

final class StudentFollowRecordRepository
{
    public function pageByStudent(int $tenantId, int $studentId): array
    {
        $rows = EducationStudentFollowRecord::query()
            ->where('tenant_id', $tenantId)
            ->where('student_id', $studentId)
            ->orderByDesc('id')
            ->get()
            ->map(static fn ($row): array => $row->toArray())
            ->all();

        return ['list' => $rows, 'total' => \count($rows)];
    }

    public function createRecord(array $data): EducationStudentFollowRecord
    {
        return EducationStudentFollowRecord::query()->create($data);
    }
}
