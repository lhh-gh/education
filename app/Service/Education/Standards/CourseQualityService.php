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

namespace App\Service\Education\Standards;

use App\Model\Education\Standards\EducationCourseFeedbackRecord;
use App\Repository\Education\Standards\CourseQualityRepository;

final class CourseQualityService
{
    public function __construct(private readonly CourseQualityRepository $quality) {}

    /**
     * @return array{quality_metric_id: int, feedback_count: int, average_score: null|string}
     */
    public function aggregateDaily(int $tenantId, int $campusId, int $courseId, string $metricDate): array
    {
        $query = EducationCourseFeedbackRecord::query()
            ->where('tenant_id', $tenantId)
            ->where('campus_id', $campusId)
            ->where('course_id', $courseId)
            ->whereDate('created_at', $metricDate);
        $feedbackCount = (int) $query->count();
        $averageScore = $feedbackCount > 0 ? number_format((float) $query->avg('score'), 2, '.', '') : null;
        $trialCount = (int) (clone $query)->where('feedback_type', 'trial')->count();
        $deliveryCount = (int) (clone $query)->where('feedback_type', 'delivery')->count();
        $metric = $this->quality->saveDaily([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'course_id' => $courseId,
            'metric_date' => $metricDate,
            'feedback_count' => $feedbackCount,
            'average_score' => $averageScore,
            'trial_feedback_count' => $trialCount,
            'delivery_feedback_count' => $deliveryCount,
        ]);

        return [
            'quality_metric_id' => (int) $metric->id,
            'feedback_count' => $feedbackCount,
            'average_score' => $averageScore,
        ];
    }
}
