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

namespace App\Service\Education\Content;

use App\Model\Education\Content\EducationLessonMaterialUsage;
use App\Model\Education\Content\EducationMaterialReadRecord;
use App\Model\Education\Content\EducationStageAchievementShowcase;
use App\Model\Education\Content\EducationStudentWork;
use App\Model\Education\Content\EducationTeacherMaterialFavorite;
use App\Repository\Education\Content\ContentMetricRepository;
use App\Service\Education\Foundation\EducationUserContext;

final class ContentMetricService
{
    public function __construct(private readonly ContentMetricRepository $metrics) {}

    /**
     * @return array{material_metric_id: int, teacher_use_count: int, guardian_read_count: int, favorite_count: int}
     */
    public function aggregateMaterialDaily(int $tenantId, ?int $campusId, int $materialId, ?int $courseId, string $metricDate): array
    {
        $usageQuery = EducationLessonMaterialUsage::query()->where('tenant_id', $tenantId)->where('material_id', $materialId)->whereDate('used_at', $metricDate);
        $readQuery = EducationMaterialReadRecord::query()->where('tenant_id', $tenantId)->where('material_id', $materialId)->whereNotNull('guardian_user_id')->whereDate('read_at', $metricDate);
        $favoriteQuery = EducationTeacherMaterialFavorite::query()->where('tenant_id', $tenantId)->where('material_id', $materialId);
        if ($campusId !== null) {
            $usageQuery->where('campus_id', $campusId);
            $readQuery->where('campus_id', $campusId);
            $favoriteQuery->where('campus_id', $campusId);
        }
        $teacherUseCount = (int) $usageQuery->count();
        $guardianReadCount = (int) $readQuery->count();
        $favoriteCount = (int) $favoriteQuery->count();
        $metric = $this->metrics->saveMaterialDaily([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'metric_date' => $metricDate,
            'material_id' => $materialId,
            'course_id' => $courseId,
            'teacher_use_count' => $teacherUseCount,
            'guardian_read_count' => $guardianReadCount,
            'favorite_count' => $favoriteCount,
        ]);

        return [
            'material_metric_id' => (int) $metric->id,
            'teacher_use_count' => $teacherUseCount,
            'guardian_read_count' => $guardianReadCount,
            'favorite_count' => $favoriteCount,
        ];
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function pageMaterialUsage(array $filters, EducationUserContext $context, int $page = 1, int $pageSize = 20): array
    {
        return $this->metrics->pageMaterialUsage($filters, $context, $page, $pageSize);
    }

    /**
     * @return array{student_work_metric_id: int, created_count: int, published_count: int, showcase_count: int}
     */
    public function aggregateStudentWorkDaily(int $tenantId, ?int $campusId, ?int $studentId, ?int $teacherId, string $metricDate): array
    {
        $workQuery = EducationStudentWork::query()->where('tenant_id', $tenantId)->whereDate('created_at', $metricDate);
        $showcaseQuery = EducationStageAchievementShowcase::query()->where('tenant_id', $tenantId)->whereDate('created_at', $metricDate);
        if ($campusId !== null) {
            $workQuery->where('campus_id', $campusId);
            $showcaseQuery->where('campus_id', $campusId);
        }
        if ($studentId !== null) {
            $workQuery->where('student_id', $studentId);
        }
        if ($teacherId !== null) {
            $workQuery->where('teacher_id', $teacherId);
        }
        $createdCount = (int) (clone $workQuery)->count();
        $publishedCount = (int) (clone $workQuery)->where('status', 'published')->count();
        $showcaseCount = (int) $showcaseQuery->count();
        $metric = $this->metrics->saveStudentWorkDaily([
            'tenant_id' => $tenantId,
            'campus_id' => $campusId,
            'metric_date' => $metricDate,
            'student_id' => $studentId,
            'teacher_id' => $teacherId,
            'created_count' => $createdCount,
            'published_count' => $publishedCount,
            'showcase_count' => $showcaseCount,
            'guardian_read_count' => 0,
        ]);

        return [
            'student_work_metric_id' => (int) $metric->id,
            'created_count' => $createdCount,
            'published_count' => $publishedCount,
            'showcase_count' => $showcaseCount,
        ];
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function pageStudentWork(array $filters, EducationUserContext $context, int $page = 1, int $pageSize = 20): array
    {
        return $this->metrics->pageStudentWork($filters, $context, $page, $pageSize);
    }
}
