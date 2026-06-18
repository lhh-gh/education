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

use App\Model\Education\Content\EducationMaterialPublishLog;
use App\Repository\Education\Content\ContentReviewRepository;
use App\Repository\Education\Content\LearningMaterialRepository;
use App\Repository\Education\Content\MaterialVersionRepository;

final class LearningMaterialService
{
    public function __construct(
        private readonly LearningMaterialRepository $materials,
        private readonly MaterialVersionRepository $versions,
        private readonly ContentReviewRepository $reviews
    ) {}

    /**
     * @param array<string, mixed> $data
     * @return array{material_id: int, current_version_id: int, status: string}
     */
    public function save(array $data): array
    {
        $material = $this->materials->save($data + ['status' => 'draft', 'guardian_visible' => false]);
        $version = $this->versions->save([
            'tenant_id' => $material->tenant_id,
            'campus_id' => $material->campus_id,
            'material_id' => $material->id,
            'version_no' => 1,
            'title' => $material->material_name,
            'content' => $data['content'] ?? null,
            'status' => 'draft',
            'snapshot_json' => $material->toArray(),
        ]);
        $material->current_version_id = $version->id;
        $material->save();

        return [
            'material_id' => (int) $material->id,
            'current_version_id' => (int) $version->id,
            'status' => $this->statusValue($material->status),
        ];
    }

    /**
     * @return array{material_id: int, current_version_id: int, status: string}
     */
    public function publish(int $tenantId, int $materialId, int $operatorId, bool $requiresApprovedReview = true): array
    {
        $material = $this->materials->findInTenant($tenantId, $materialId);
        if ($requiresApprovedReview && ! $this->reviews->hasApprovedReview($tenantId, 'learning_material', $materialId)) {
            throw new \RuntimeException('material requires approved review before publish', 409);
        }
        $version = $this->versions->findInTenant($tenantId, (int) $material->current_version_id);
        $fromStatus = $this->statusValue($material->status);
        $material->status = 'published';
        $material->save();
        $version->status = 'published';
        $version->published_at = date('Y-m-d H:i:s');
        $version->save();
        EducationMaterialPublishLog::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $material->campus_id,
            'material_id' => $materialId,
            'material_version_id' => $version->id,
            'from_status' => $fromStatus,
            'to_status' => 'published',
            'operator_id' => $operatorId,
        ]);

        return [
            'material_id' => $materialId,
            'current_version_id' => (int) $version->id,
            'status' => 'published',
        ];
    }

    /**
     * @return array{material_id: int, status: string}
     */
    public function withdraw(int $tenantId, int $materialId, int $operatorId = 0): array
    {
        $material = $this->materials->findInTenant($tenantId, $materialId);
        $fromStatus = $this->statusValue($material->status);
        $material->status = 'withdrawn';
        $material->save();
        EducationMaterialPublishLog::query()->create([
            'tenant_id' => $tenantId,
            'campus_id' => $material->campus_id,
            'material_id' => $materialId,
            'material_version_id' => $material->current_version_id,
            'from_status' => $fromStatus,
            'to_status' => 'withdrawn',
            'operator_id' => $operatorId,
        ]);

        return ['material_id' => $materialId, 'status' => 'withdrawn'];
    }

    /**
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(int $tenantId, array $filters = [], int $page = 1, int $pageSize = 20): array
    {
        return $this->materials->page($tenantId, $filters, $page, $pageSize);
    }

    private function statusValue(mixed $status): string
    {
        return $status instanceof \BackedEnum ? (string) $status->value : (string) $status;
    }
}
