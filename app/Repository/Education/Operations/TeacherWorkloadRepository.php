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

use App\Model\Education\Operations\EducationTeacherWorkloadRecord;

final class TeacherWorkloadRepository
{
    public function createOrUpdateLessonTeacherRecord(array $data): EducationTeacherWorkloadRecord
    {
        return EducationTeacherWorkloadRecord::query()->updateOrCreate([
            'tenant_id' => $data['tenant_id'],
            'lesson_id' => $data['lesson_id'],
            'teacher_id' => $data['teacher_id'],
            'workload_type' => $data['workload_type'],
        ], $data);
    }

    public function summaryByTeacher(int $tenantId, int $teacherId): array
    {
        $row = EducationTeacherWorkloadRecord::query()
            ->where('tenant_id', $tenantId)
            ->where('teacher_id', $teacherId)
            ->selectRaw('COUNT(*) as row_count, SUM(credits) as credits, SUM(student_count) as student_count')
            ->first();

        return [
            'row_count' => (int) ($row->row_count ?? 0),
            'credits' => number_format((float) ($row->credits ?? 0), 2, '.', ''),
            'student_count' => (int) ($row->student_count ?? 0),
        ];
    }

    public function summaryByCampus(int $tenantId, ?int $campusId = null): array
    {
        $query = EducationTeacherWorkloadRecord::query()->where('tenant_id', $tenantId);
        if ($campusId !== null) {
            $query->where('campus_id', $campusId);
        }
        $row = $query->selectRaw('COUNT(*) as row_count, SUM(credits) as credits')->first();

        return [
            'row_count' => (int) ($row->row_count ?? 0),
            'credits' => number_format((float) ($row->credits ?? 0), 2, '.', ''),
        ];
    }
}
