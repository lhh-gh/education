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

namespace HyperfTests\Unit\Education\Content;

use App\Model\Education\Content\EducationLessonMaterialUsage;
use App\Model\Education\Content\EducationMaterialReadRecord;
use App\Model\Education\Content\EducationMaterialUsageMetricDaily;
use App\Model\Education\Content\EducationStudentWorkMetricDaily;
use App\Model\Education\Content\EducationTeacherMaterialFavorite;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Content\ContentMetricService;

/**
 * @internal
 * @coversNothing
 */
final class ContentMetricServiceTest extends ContentTestCase
{
    public function testAggregateMaterialDailyUsesCampusScope(): void
    {
        [$tenant, $campus] = $this->tenantCampus('content_metric_material_aggregate_scope');
        $hiddenCampus = $this->campus($tenant, 'hidden-content-metric-material-aggregate');
        $metricDate = '2026-06-20';
        foreach ([$campus->id, $hiddenCampus->id] as $index => $campusId) {
            EducationLessonMaterialUsage::query()->create([
                'tenant_id' => $tenant->id,
                'campus_id' => $campusId,
                'lesson_id' => 7001 + $index,
                'teacher_id' => 8001 + $index,
                'material_id' => 901,
                'material_version_id' => 1901 + $index,
                'usage_type' => 'preview',
                'used_at' => $metricDate . ' 09:00:00',
            ]);
            EducationMaterialReadRecord::query()->create([
                'tenant_id' => $tenant->id,
                'campus_id' => $campusId,
                'material_id' => 901,
                'material_version_id' => 2901 + $index,
                'student_id' => 3001 + $index,
                'guardian_user_id' => 4001 + $index,
                'read_at' => $metricDate . ' 10:00:00',
            ]);
            EducationTeacherMaterialFavorite::query()->create([
                'tenant_id' => $tenant->id,
                'campus_id' => $campusId,
                'teacher_id' => 5001 + $index,
                'material_id' => 901,
                'favorited_at' => $metricDate . ' 11:00:00',
            ]);
        }

        $metric = make(ContentMetricService::class)->aggregateMaterialDaily((int) $tenant->id, (int) $campus->id, 901, 301, $metricDate);

        self::assertSame(1, $metric['teacher_use_count']);
        self::assertSame(1, $metric['guardian_read_count']);
        self::assertSame(1, $metric['favorite_count']);
    }

    public function testPageMaterialUsageUsesCurrentCampusScope(): void
    {
        [$tenant, $campus] = $this->tenantCampus('content_metric_material_scope');
        $hiddenCampus = $this->campus($tenant, 'hidden-content-metric-material');
        $visible = EducationMaterialUsageMetricDaily::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'metric_date' => '2026-06-20',
            'material_id' => 101,
            'course_id' => 301,
            'teacher_use_count' => 2,
            'guardian_read_count' => 3,
            'favorite_count' => 1,
        ]);
        EducationMaterialUsageMetricDaily::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $hiddenCampus->id,
            'metric_date' => '2026-06-20',
            'material_id' => 102,
            'course_id' => 302,
            'teacher_use_count' => 5,
            'guardian_read_count' => 8,
            'favorite_count' => 2,
        ]);
        $context = $this->context((int) $tenant->id, EducationRoleCode::Teacher, [(int) $campus->id], 9903);

        $page = make(ContentMetricService::class)->pageMaterialUsage([], $context, 1, 20);

        self::assertSame(1, $page['total']);
        self::assertSame((int) $visible->id, (int) $page['list'][0]['id']);
    }

    public function testPageStudentWorkUsesCurrentCampusScope(): void
    {
        [$tenant, $campus] = $this->tenantCampus('content_metric_work_scope');
        $hiddenCampus = $this->campus($tenant, 'hidden-content-metric-work');
        $visible = EducationStudentWorkMetricDaily::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'metric_date' => '2026-06-20',
            'student_id' => 1201,
            'teacher_id' => 701,
            'created_count' => 2,
            'published_count' => 1,
            'showcase_count' => 1,
            'guardian_read_count' => 3,
        ]);
        EducationStudentWorkMetricDaily::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $hiddenCampus->id,
            'metric_date' => '2026-06-20',
            'student_id' => 1202,
            'teacher_id' => 702,
            'created_count' => 4,
            'published_count' => 2,
            'showcase_count' => 1,
            'guardian_read_count' => 5,
        ]);
        $context = $this->context((int) $tenant->id, EducationRoleCode::Teacher, [(int) $campus->id], 9903);

        $page = make(ContentMetricService::class)->pageStudentWork([], $context, 1, 20);

        self::assertSame(1, $page['total']);
        self::assertSame((int) $visible->id, (int) $page['list'][0]['id']);
    }
}
