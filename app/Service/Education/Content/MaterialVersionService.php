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

use App\Repository\Education\Content\LearningMaterialRepository;
use App\Repository\Education\Content\MaterialVersionRepository;
use App\Service\Education\Foundation\EducationUserContext;

final class MaterialVersionService
{
    public function __construct(
        private readonly LearningMaterialRepository $materials,
        private readonly MaterialVersionRepository $versions
    ) {}

    /**
     * @param array<string, mixed> $data
     * @return array{material_version_id: int, version_no: int, status: string}
     */
    public function save(EducationUserContext $context, array $data): array
    {
        $materialId = (int) $data['material_id'];
        $material = $this->materials->findInContext($context, $materialId);
        $tenantId = (int) $material->tenant_id;
        $campusId = $material->campus_id === null ? null : (int) $material->campus_id;
        $versionId = isset($data['material_version_id']) ? (int) $data['material_version_id'] : null;
        if ($versionId !== null) {
            $existing = $this->versions->findInContext($context, $versionId);
            if ($this->statusValue($existing->status) === 'published') {
                unset($data['material_version_id'], $data['id']);
                $data['version_no'] = $this->versions->nextVersionNo($tenantId, $materialId);
                $data['status'] = 'draft';
            } else {
                $data['id'] = $versionId;
                $data['version_no'] = $existing->version_no;
            }
        }
        $data['tenant_id'] = $tenantId;
        $data['campus_id'] = $campusId;

        $version = $this->versions->save($data + [
            'version_no' => 1,
            'status' => 'draft',
            'snapshot_json' => [
                'title' => $data['title'] ?? '',
                'content' => $data['content'] ?? null,
            ],
        ]);
        $material->current_version_id = $version->id;
        $material->save();

        return [
            'material_version_id' => (int) $version->id,
            'version_no' => (int) $version->version_no,
            'status' => $this->statusValue($version->status),
        ];
    }

    /**
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(int $materialId, EducationUserContext $context, int $page = 1, int $pageSize = 20): array
    {
        return $this->versions->page($materialId, $context, $page, $pageSize);
    }

    /**
     * @return array<string, mixed>
     */
    public function detail(EducationUserContext $context, int $versionId): array
    {
        return $this->versions->findInContext($context, $versionId)->toArray();
    }

    private function statusValue(mixed $status): string
    {
        return $status instanceof \BackedEnum ? (string) $status->value : (string) $status;
    }
}
