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
    public function testListForVersionUsesCurrentCampusScope(): void
    {
        [$tenant, $campus] = $this->tenantCampus('content_attachment_list_scope');
        $hiddenCampus = $this->campus($tenant, 'hidden-content-attachment-list');
        $versionId = 301;
        $visible = EducationLearningMaterialAttachment::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'material_version_id' => $versionId,
            'file_name' => 'visible-list.pdf',
            'file_url' => '/visible-list.pdf',
            'file_type' => 'pdf',
            'file_size' => 128,
            'sort_order' => 1,
        ]);
        EducationLearningMaterialAttachment::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $hiddenCampus->id,
            'material_version_id' => $versionId,
            'file_name' => 'hidden-list.pdf',
            'file_url' => '/hidden-list.pdf',
            'file_type' => 'pdf',
            'file_size' => 256,
            'sort_order' => 2,
        ]);
        $context = $this->context((int) $tenant->id, EducationRoleCode::Teacher, [(int) $campus->id], 9901);

        $list = make(MaterialAttachmentService::class)->listForVersion($context, $versionId);

        self::assertCount(1, $list);
        self::assertSame((int) $visible->id, (int) $list[0]['id']);
    }

    public function testReplaceForVersionUsesCurrentCampusScope(): void
    {
        [$tenant, $campus] = $this->tenantCampus('content_attachment_replace_scope');
        $hiddenCampus = $this->campus($tenant, 'hidden-content-attachment-replace');
        $versionId = 401;
        EducationLearningMaterialAttachment::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'material_version_id' => $versionId,
            'file_name' => 'old-visible.pdf',
            'file_url' => '/old-visible.pdf',
            'file_type' => 'pdf',
            'file_size' => 128,
            'sort_order' => 1,
        ]);
        $hidden = EducationLearningMaterialAttachment::query()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $hiddenCampus->id,
            'material_version_id' => $versionId,
            'file_name' => 'hidden.pdf',
            'file_url' => '/hidden.pdf',
            'file_type' => 'pdf',
            'file_size' => 256,
            'sort_order' => 2,
        ]);
        $context = $this->context((int) $tenant->id, EducationRoleCode::Teacher, [(int) $campus->id], 9901);

        make(MaterialAttachmentService::class)->replaceForVersion($context, $versionId, [[
            'file_name' => 'new-visible.pdf',
            'file_url' => '/new-visible.pdf',
            'file_type' => 'pdf',
        ]]);

        self::assertTrue(EducationLearningMaterialAttachment::query()->whereKey($hidden->id)->exists());
        self::assertSame(1, EducationLearningMaterialAttachment::query()
            ->where('tenant_id', $tenant->id)
            ->where('campus_id', $campus->id)
            ->where('material_version_id', $versionId)
            ->count());
        self::assertSame('new-visible.pdf', EducationLearningMaterialAttachment::query()
            ->where('tenant_id', $tenant->id)
            ->where('campus_id', $campus->id)
            ->where('material_version_id', $versionId)
            ->firstOrFail()
            ->file_name);
    }

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
