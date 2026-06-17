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

use App\Model\Education\Family\EducationServiceQualityMetric;
use App\Repository\Education\Family\ServiceQualityRepository;
use Carbon\Carbon;

final class ServiceQualityService
{
    public function __construct(private readonly ServiceQualityRepository $repository) {}

    public function incrementComment(int $tenantId, ?int $campusId, int $teacherId, int $studentId): void
    {
        $this->increment($tenantId, $campusId, $teacherId, $studentId, 'comment_count');
    }

    public function incrementHomeworkReview(int $tenantId, ?int $campusId, int $teacherId, int $studentId): void
    {
        $this->increment($tenantId, $campusId, $teacherId, $studentId, 'homework_review_count');
    }

    public function incrementReport(int $tenantId, ?int $campusId, int $studentId): void
    {
        $this->increment($tenantId, $campusId, null, $studentId, 'report_count');
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    public function dashboard(array $filters): array
    {
        return EducationServiceQualityMetric::query()
            ->when(isset($filters['tenant_id']), static fn ($query) => $query->where('tenant_id', (int) $filters['tenant_id']))
            ->when(isset($filters['campus_id']), static fn ($query) => $query->where('campus_id', (int) $filters['campus_id']))
            ->orderByDesc('metric_date')
            ->limit(100)
            ->get()
            ->map(static fn (EducationServiceQualityMetric $row): array => $row->toArray())
            ->all();
    }

    private function increment(int $tenantId, ?int $campusId, ?int $teacherId, ?int $studentId, string $column): void
    {
        $metric = $this->repository->metricForDate($tenantId, $campusId, Carbon::now()->toDateString(), $teacherId, $studentId);
        $metric->{$column} = (int) $metric->{$column} + 1;
        $metric->save();
    }
}
