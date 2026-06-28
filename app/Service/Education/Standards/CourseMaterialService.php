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

namespace App\Service\Education\Standards;

use App\Repository\Education\Standards\CourseMaterialRepository;
use App\Service\Education\Foundation\EducationUserContext;

final class CourseMaterialService
{
    public function __construct(private readonly CourseMaterialRepository $materials) {}

    /**
     * @param array<string, mixed> $data
     * @return array{material_id: int, status: string}
     */
    public function save(array $data, ?EducationUserContext $context = null): array
    {
        $material = $this->materials->save($data + ['status' => 'draft', 'guardian_visible' => false], $context);

        return ['material_id' => (int) $material->id, 'status' => (string) $material->status];
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(array $filters, EducationUserContext $context, int $page = 1, int $pageSize = 20): array
    {
        return $this->materials->page($filters, $context, $page, $pageSize);
    }
}
