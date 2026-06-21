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

namespace HyperfTests\Unit\Education\Standards;

use App\Model\Education\Standards\EducationCourseMaterial;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Standards\CourseMaterialService;

/**
 * @internal
 * @coversNothing
 */
final class CourseMaterialServiceTest extends StandardsTestCase
{
    public function testPageUsesCurrentCampusScope(): void
    {
        [$tenant, $campus] = $this->tenantCampus('standards_material_scope');
        $hiddenCampus = $this->campus($tenant, 'hidden-standards-material');
        $visible = EducationCourseMaterial::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'material_code' => 'MAT-VISIBLE',
            'material_name' => 'Visible Material',
            'course_id' => 301,
            'material_type' => 'file',
            'file_url' => '/visible.pdf',
            'status' => 'draft',
            'guardian_visible' => true,
        ]);
        EducationCourseMaterial::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $hiddenCampus->id,
            'material_code' => 'MAT-HIDDEN',
            'material_name' => 'Hidden Material',
            'course_id' => 302,
            'material_type' => 'file',
            'file_url' => '/hidden.pdf',
            'status' => 'draft',
            'guardian_visible' => false,
        ]);
        $context = $this->context((int) $tenant->id, EducationRoleCode::Teacher, [(int) $campus->id], 9902);

        $page = make(CourseMaterialService::class)->page([], $context, 1, 20);

        self::assertSame(1, $page['total']);
        self::assertSame((int) $visible->id, (int) $page['list'][0]['id']);
    }
}
