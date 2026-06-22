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
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Content\LearningMaterialService;
use Hyperf\Database\Model\ModelNotFoundException;

/**
 * @internal
 * @coversNothing
 */
final class LearningMaterialServiceTest extends ContentTestCase
{
    public function testPageUsesCurrentCampusScope(): void
    {
        [$tenant, $campus] = $this->tenantCampus('content_material_scope');
        $hiddenCampus = $this->campus($tenant, 'hidden-content-material');
        $visible = EducationLearningMaterial::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'material_code' => 'MAT-VISIBLE',
            'material_name' => 'Visible Material',
            'course_id' => 301,
            'material_type' => 'worksheet',
            'status' => 'draft',
            'guardian_visible' => true,
        ]);
        EducationLearningMaterial::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $hiddenCampus->id,
            'material_code' => 'MAT-HIDDEN',
            'material_name' => 'Hidden Material',
            'course_id' => 302,
            'material_type' => 'video',
            'status' => 'draft',
            'guardian_visible' => false,
        ]);
        $context = $this->context((int) $tenant->id, EducationRoleCode::Teacher, [(int) $campus->id], 9903);

        $page = make(LearningMaterialService::class)->page([], $context, 1, 20);

        self::assertSame(1, $page['total']);
        self::assertSame((int) $visible->id, (int) $page['list'][0]['id']);
    }

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

        $context = $this->context((int) $tenant->id, EducationRoleCode::Teacher, [(int) $campus->id], 9001);

        make(LearningMaterialService::class)->publish($context, $created['material_id'], 9001, true);
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
        $context = $this->context((int) $tenant->id, EducationRoleCode::Teacher, [(int) $campus->id], 9001);

        $result = make(LearningMaterialService::class)->publish($context, $created['material_id'], 9001, true);

        self::assertSame('published', $result['status']);
        self::assertSame('published', EducationLearningMaterial::query()->find($created['material_id'])->status->value);
        self::assertSame('published', EducationLearningMaterialVersion::query()->find($result['current_version_id'])->status->value);
    }

    public function testPublishUsesCurrentCampusScope(): void
    {
        [$tenant, $campus] = $this->tenantCampus('content_material_publish_scope');
        $hiddenCampus = $this->campus($tenant, 'hidden-content-material-publish');
        $created = make(LearningMaterialService::class)->save([
            'tenant_id' => $tenant->id,
            'campus_id' => $hiddenCampus->id,
            'material_code' => 'ART-HIDDEN-001',
            'material_name' => 'Hidden Material',
            'course_id' => 301,
            'material_type' => 'video',
            'guardian_visible' => true,
        ]);
        $context = $this->context((int) $tenant->id, EducationRoleCode::Teacher, [(int) $campus->id], 9001);

        $this->expectException(ModelNotFoundException::class);

        make(LearningMaterialService::class)->publish($context, $created['material_id'], 9001, false);
    }
}
