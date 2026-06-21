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

use App\Repository\Education\Content\MaterialRelationRepository;
use App\Service\Education\Foundation\EducationUserContext;

final class MaterialRelationService
{
    public function __construct(private readonly MaterialRelationRepository $relations) {}

    /**
     * @param list<array<string, mixed>> $relations
     */
    public function saveMaterialRelations(int $tenantId, ?int $campusId, int $materialId, array $relations): void
    {
        $this->relations->replaceForMaterial($tenantId, $campusId, $materialId, $relations);
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(array $filters, EducationUserContext $context, int $page = 1, int $pageSize = 20): array
    {
        return $this->relations->page($filters, $context, $page, $pageSize);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function pageByTarget(EducationUserContext $context, string $targetType, int $targetId): array
    {
        return $this->relations->pageByTarget($context, $targetType, $targetId);
    }
}
