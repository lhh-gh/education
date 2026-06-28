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
use App\Service\Education\Foundation\EducationScopeQuery;
use App\Service\Education\Foundation\EducationUserContext;

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

    public function summaryByTeacherContext(EducationUserContext $context, int $teacherId): array
    {
        $row = $this->scopedQuery([], $context)
            ->where('teacher_id', $teacherId)
            ->selectRaw('COUNT(*) as row_count, SUM(credits) as credits, SUM(student_count) as student_count')
            ->first();

        return [
            'row_count' => (int) ($row->row_count ?? 0),
            'credits' => number_format((float) ($row->credits ?? 0), 2, '.', ''),
            'student_count' => (int) ($row->student_count ?? 0),
        ];
    }

    public function pageReport(array $params, EducationUserContext $context): array
    {
        $query = $this->scopedQuery($params, $context);
        $this->applyReportFilters($query, $params);

        return $query->orderByDesc('recorded_at')->get()->map(static fn ($row): array => $row->toArray())->all();
    }

    public function summaryReport(array $params, EducationUserContext $context): array
    {
        $query = $this->scopedQuery($params, $context);
        $this->applyReportFilters($query, $params);
        $row = $query
            ->selectRaw('COUNT(*) as row_count, SUM(credits) as total_credits, SUM(student_count) as student_count, SUM(present_count) as present_count')
            ->first();

        return [
            'teacher_id' => isset($params['teacher_id']) && $params['teacher_id'] !== '' ? (int) $params['teacher_id'] : null,
            'total_credits' => number_format((float) ($row->total_credits ?? 0), 2, '.', ''),
            'row_count' => (int) ($row->row_count ?? 0),
            'student_count' => (int) ($row->student_count ?? 0),
            'present_count' => (int) ($row->present_count ?? 0),
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

    private function scopedQuery(array $params, EducationUserContext $context): mixed
    {
        $query = EducationTeacherWorkloadRecord::query();

        return (new EducationScopeQuery())->applyTenantCampus($query, $params, $context);
    }

    private function applyReportFilters(mixed $query, array $params): void
    {
        foreach (['campus_id', 'teacher_id', 'workload_type'] as $field) {
            if (isset($params[$field]) && $params[$field] !== '') {
                $query->where($field, $params[$field]);
            }
        }
        if (isset($params['start_at']) && $params['start_at'] !== '') {
            $query->where('recorded_at', '>=', $params['start_at']);
        }
        if (isset($params['end_at']) && $params['end_at'] !== '') {
            $query->where('recorded_at', '<=', $params['end_at']);
        }
    }
}
