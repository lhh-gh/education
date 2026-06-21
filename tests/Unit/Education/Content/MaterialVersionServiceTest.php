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

use App\Model\Education\Content\EducationLearningMaterialVersion;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Content\LearningMaterialService;
use App\Service\Education\Content\MaterialVersionService;

/**
 * @internal
 * @coversNothing
 */
final class MaterialVersionServiceTest extends ContentTestCase
{
    public function testPageUsesCurrentCampusScope(): void
    {
        [$tenant, $campus] = $this->tenantCampus('content_version_scope');
        $hiddenCampus = $this->campus($tenant, 'hidden-content-version');
        $visible = make(LearningMaterialService::class)->save([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'material_code' => 'MAT-VERSION-VISIBLE',
            'material_name' => 'Visible Version Material',
            'course_id' => 301,
            'material_type' => 'worksheet',
            'guardian_visible' => true,
        ]);
        $hidden = make(LearningMaterialService::class)->save([
            'tenant_id' => $tenant->id,
            'campus_id' => $hiddenCampus->id,
            'material_code' => 'MAT-VERSION-HIDDEN',
            'material_name' => 'Hidden Version Material',
            'course_id' => 302,
            'material_type' => 'video',
            'guardian_visible' => true,
        ]);
        $context = $this->context((int) $tenant->id, EducationRoleCode::Teacher, [(int) $campus->id], 9905);
        $service = make(MaterialVersionService::class);

        $page = $service->page($visible['material_id'], $context, 1, 20);
        $hiddenPage = $service->page($hidden['material_id'], $context, 1, 20);

        self::assertSame(1, $page['total']);
        self::assertSame($visible['current_version_id'], (int) $page['list'][0]['id']);
        self::assertSame(0, $hiddenPage['total']);
    }

    public function testPublishedVersionIsImmutable(): void
    {
        [$tenant, $campus] = $this->tenantCampus('content_version_immutable');
        $created = make(LearningMaterialService::class)->save([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'material_code' => 'ART-SHAPE-001',
            'material_name' => 'Shape Practice',
            'course_id' => 302,
            'material_type' => 'worksheet',
            'guardian_visible' => true,
        ]);
        EducationLearningMaterialVersion::query()->whereKey($created['current_version_id'])->update(['status' => 'published']);

        $edited = make(MaterialVersionService::class)->save([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'material_id' => $created['material_id'],
            'material_version_id' => $created['current_version_id'],
            'title' => 'Shape Practice 2026',
            'content' => 'new content',
        ]);

        self::assertNotSame($created['current_version_id'], $edited['material_version_id']);
        self::assertSame(2, $edited['version_no']);
        self::assertSame('draft', $edited['status']);
        self::assertSame('Shape Practice', (string) EducationLearningMaterialVersion::query()->find($created['current_version_id'])->title);
    }
}
