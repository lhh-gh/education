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

namespace App\Repository\Education\Ai;

use App\Model\Education\Ai\EducationAiMetricCatalog;

final class AiMetricCatalogRepository
{
    public function findEnabledByCode(int $tenantId, string $metricCode): ?EducationAiMetricCatalog
    {
        return EducationAiMetricCatalog::query()
            ->where('tenant_id', $tenantId)
            ->where('metric_code', $metricCode)
            ->where('status', 'enabled')
            ->first();
    }
}
