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
use App\Model\Enums\Education\Foundation\EducationRoleCode;
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
        foreach (['campus_id', 'teacher_id', 'workload_type'] as $field) {
            if (isset($params[$field]) && $params[$field] !== '') {
                $query->where($field, $params[$field]);
            }
        }

        return $query->orderByDesc('recorded_at')->get()->map(static fn ($row): array => $row->toArray())->all();
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
        if ($context->platformAccess) {
            if (isset($params['tenant_id']) && $params['tenant_id'] !== '') {
                $query->where('tenant_id', (int) $params['tenant_id']);
            }

            return $query;
        }

        if ($context->tenantId === null) {
            return $query->whereRaw('1 = 0');
        }

        $query->where('tenant_id', $context->tenantId);
        if ($context->roleCode !== EducationRoleCode::TenantAdmin) {
            $query->whereIn('campus_id', $context->campusIds ?: [0]);
        }

        return $query;
    }
}
