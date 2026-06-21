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

use App\Model\Education\Content\EducationLearningMaterialAttachment;
use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Service\Education\Content\MaterialAttachmentService;

/**
 * @internal
 * @coversNothing
 */
final class MaterialAttachmentServiceTest extends ContentTestCase
{
    public function testPageUsesCurrentCampusScope(): void
    {
        [$tenant, $campus] = $this->tenantCampus('content_attachment_scope');
        $hiddenCampus = $this->campus($tenant, 'hidden-content-attachment');
        $visible = EducationLearningMaterialAttachment::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'material_version_id' => 101,
            'file_name' => 'visible.pdf',
            'file_url' => '/visible.pdf',
            'file_type' => 'pdf',
            'file_size' => 128,
            'sort_order' => 1,
        ]);
        EducationLearningMaterialAttachment::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $hiddenCampus->id,
            'material_version_id' => 102,
            'file_name' => 'hidden.pdf',
            'file_url' => '/hidden.pdf',
            'file_type' => 'pdf',
            'file_size' => 256,
            'sort_order' => 1,
        ]);
        $context = $this->context((int) $tenant->id, EducationRoleCode::Teacher, [(int) $campus->id], 9901);

        $page = make(MaterialAttachmentService::class)->page([], $context, 1, 20);

        self::assertSame(1, $page['total']);
        self::assertSame((int) $visible->id, (int) $page['list'][0]['id']);
    }
}
