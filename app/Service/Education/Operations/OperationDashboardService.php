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

namespace App\Service\Education\Operations;

use App\Repository\Education\Operations\DailyOperationMetricRepository;
use App\Service\Education\Foundation\EducationUserContext;

final class OperationDashboardService
{
    public function __construct(private readonly DailyOperationMetricRepository $repository) {}

    public function upsertDailyMetric(array $data): array
    {
        return $this->repository->upsertDailyMetric($data)->toArray();
    }

    public function overview(EducationUserContext $context, ?int $campusId = null): array
    {
        return [
            'summary' => $this->repository->dashboardSummary($context, $campusId),
            'trend' => $this->repository->trend($context, $campusId),
        ];
    }
}
