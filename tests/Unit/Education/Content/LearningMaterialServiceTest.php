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

use App\Model\Education\Content\EducationContentReviewRecord;
use App\Model\Education\Content\EducationLearningMaterial;
use App\Model\Education\Content\EducationLearningMaterialVersion;
use App\Service\Education\Content\LearningMaterialService;

/**
 * @internal
 * @coversNothing
 */
final class LearningMaterialServiceTest extends ContentTestCase
{
    public function testMaterialRequiresReviewBeforePublishWhenEnabled(): void
    {
        [$tenant, $campus] = $this->tenantCampus('content_material_review');
        $created = make(LearningMaterialService::class)->save([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'material_code' => 'ART-LINE-001',
            'material_name' => 'Line Practice',
            'course_id' => 301,
            'material_type' => 'worksheet',
            'guardian_visible' => true,
            'summary' => 'Line worksheet',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(409);
        $this->expectExceptionMessage('material requires approved review before publish');

        make(LearningMaterialService::class)->publish((int) $tenant->id, $created['material_id'], 9001, true);
    }

    public function testApprovedReviewAllowsPublish(): void
    {
        [$tenant, $campus] = $this->tenantCampus('content_material_approved');
        $created = make(LearningMaterialService::class)->save([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'material_code' => 'ART-COLOR-001',
            'material_name' => 'Color Wheel',
            'course_id' => 301,
            'material_type' => 'video',
            'guardian_visible' => true,
        ]);
        EducationContentReviewRecord::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'business_type' => 'learning_material',
            'business_id' => $created['material_id'],
            'reviewer_id' => 9002,
            'status' => 'approved',
            'reviewed_at' => '2026-06-10 10:00:00',
        ]);

        $result = make(LearningMaterialService::class)->publish((int) $tenant->id, $created['material_id'], 9001, true);

        self::assertSame('published', $result['status']);
        self::assertSame('published', EducationLearningMaterial::query()->find($created['material_id'])->status->value);
        self::assertSame('published', EducationLearningMaterialVersion::query()->find($result['current_version_id'])->status->value);
    }
}
