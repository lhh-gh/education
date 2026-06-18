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

namespace App\Service\Education\Admissions;

use App\Repository\Education\Admissions\AdmissionMetricRepository;
use App\Service\Education\Foundation\EducationUserContext;

final class AdmissionDashboardService
{
    public function __construct(private readonly AdmissionMetricRepository $repository) {}

    public function overview(array $filters, EducationUserContext $context): array
    {
        $summary = $this->repository->summary($filters, $context);
        $trialCount = max(1, $summary['trial_count']);
        $leadCount = max(1, $summary['new_leads_count']);

        return $summary + [
            'trial_attendance_rate' => round($summary['trial_attended_count'] / $trialCount, 4),
            'conversion_rate' => round($summary['converted_count'] / $leadCount, 4),
        ];
    }
}
