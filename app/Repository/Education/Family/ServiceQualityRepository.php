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

use App\Model\Education\Family\EducationServiceQualityMetric;

final class ServiceQualityRepository
{
    public function metricForDate(int $tenantId, ?int $campusId, string $date, ?int $teacherId, ?int $studentId): EducationServiceQualityMetric
    {
        $metric = EducationServiceQualityMetric::query()
            ->where('tenant_id', $tenantId)
            ->where('campus_id', $campusId)
            ->where('metric_date', $date)
            ->where('teacher_id', $teacherId)
            ->where('student_id', $studentId)
            ->first();

        if ($metric instanceof EducationServiceQualityMetric) {
            return $metric;
        }

        return EducationServiceQualityMetric::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'metric_date' => $date,
            'teacher_id' => $teacherId,
            'student_id' => $studentId,
            'comment_count' => 0,
            'homework_review_count' => 0,
            'report_count' => 0,
        ]);
    }
}
