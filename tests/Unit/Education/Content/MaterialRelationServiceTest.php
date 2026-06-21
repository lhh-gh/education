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

use App\Model\Education\Content\EducationLearningMaterialRelation;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Content\LearningMaterialService;
use App\Service\Education\Content\MaterialRelationService;

/**
 * @internal
 * @coversNothing
 */
final class MaterialRelationServiceTest extends ContentTestCase
{
    public function testPageUsesCurrentCampusScope(): void
    {
        [$tenant, $campus] = $this->tenantCampus('content_relation_scope');
        $hiddenCampus = $this->campus($tenant, 'hidden-content-relation');
        $visibleMaterial = make(LearningMaterialService::class)->save([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'material_code' => 'MAT-RELATION-VISIBLE',
            'material_name' => 'Visible Relation Material',
            'course_id' => 301,
            'material_type' => 'worksheet',
            'guardian_visible' => true,
        ]);
        $hiddenMaterial = make(LearningMaterialService::class)->save([
            'tenant_id' => $tenant->id,
            'campus_id' => $hiddenCampus->id,
            'material_code' => 'MAT-RELATION-HIDDEN',
            'material_name' => 'Hidden Relation Material',
            'course_id' => 302,
            'material_type' => 'video',
            'guardian_visible' => true,
        ]);
        $visible = EducationLearningMaterialRelation::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'material_id' => $visibleMaterial['material_id'],
            'target_type' => 'course',
            'target_id' => 501,
            'relation_note' => 'visible relation',
        ]);
        EducationLearningMaterialRelation::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $hiddenCampus->id,
            'material_id' => $hiddenMaterial['material_id'],
            'target_type' => 'course',
            'target_id' => 501,
            'relation_note' => 'hidden relation',
        ]);
        $context = $this->context((int) $tenant->id, EducationRoleCode::Teacher, [(int) $campus->id], 9906);
        $service = make(MaterialRelationService::class);

        $page = $service->page(['target_type' => 'course', 'target_id' => 501], $context, 1, 20);
        $targetRows = $service->pageByTarget($context, 'course', 501);

        self::assertSame(1, $page['total']);
        self::assertSame((int) $visible->id, (int) $page['list'][0]['id']);
        self::assertCount(1, $targetRows);
        self::assertSame((int) $visible->id, (int) $targetRows[0]['id']);
    }
}
