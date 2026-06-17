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

use App\Model\Education\Family\EducationLearningReport;
use App\Model\Education\Family\EducationLearningReportItem;
use App\Service\Education\Foundation\EducationUserContext;

final class LearningReportRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): EducationLearningReport
    {
        return EducationLearningReport::query()->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(EducationLearningReport $report, array $data): EducationLearningReport
    {
        $report->fill($data);
        $report->save();

        return $report;
    }

    public function find(int $id, int $tenantId): ?EducationLearningReport
    {
        $row = EducationLearningReport::query()->where('tenant_id', $tenantId)->whereKey($id)->first();

        return $row instanceof EducationLearningReport ? $row : null;
    }

    public function itemCount(int $reportId, int $tenantId): int
    {
        return EducationLearningReportItem::query()
            ->where('tenant_id', $tenantId)
            ->where('learning_report_id', $reportId)
            ->count();
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    public function replaceItems(EducationLearningReport $report, array $items): void
    {
        EducationLearningReportItem::query()
            ->where('tenant_id', (int) $report->tenant_id)
            ->where('learning_report_id', (int) $report->id)
            ->delete();

        foreach ($items as $index => $item) {
            EducationLearningReportItem::query()->create([
                'tenant_id' => (int) $report->tenant_id,
                'campus_id' => $report->campus_id,
                'learning_report_id' => (int) $report->id,
                'item_type' => (string) ($item['item_type'] ?? 'summary'),
                'title' => (string) ($item['title'] ?? ''),
                'content' => (string) ($item['content'] ?? ''),
                'sort_order' => (int) ($item['sort_order'] ?? $index),
                'created_by' => $report->created_by,
                'updated_by' => $report->updated_by,
            ]);
        }
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function visibleForGuardian(int $studentId, array $filters, EducationUserContext $context): array
    {
        $query = EducationLearningReport::query()
            ->where('tenant_id', $context->tenantId)
            ->where('student_id', $studentId)
            ->where('status', 'published');
        $total = (clone $query)->count();
        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($filters['pageSize'] ?? 20)));
        $list = $query->orderByDesc('id')->forPage($page, $pageSize)->get()->map(static fn (EducationLearningReport $row): array => $row->toArray())->all();

        return ['list' => $list, 'total' => $total];
    }
}
