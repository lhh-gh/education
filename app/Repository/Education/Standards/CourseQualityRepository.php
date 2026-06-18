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

namespace App\Repository\Education\Standards;

use App\Model\Education\Standards\EducationCourseQualityMetricDaily;

final class CourseQualityRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function saveDaily(array $data): EducationCourseQualityMetricDaily
    {
        return EducationCourseQualityMetricDaily::query()->updateOrCreate([
            'tenant_id' => $data['tenant_id'],
            'campus_id' => $data['campus_id'],
            'course_id' => $data['course_id'],
            'metric_date' => $data['metric_date'],
        ], $data);
    }
}
