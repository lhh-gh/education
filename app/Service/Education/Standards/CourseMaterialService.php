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

final class CourseMaterialService
{
    public function __construct(private readonly CourseMaterialRepository $materials) {}

    /**
     * @param array<string, mixed> $data
     * @return array{material_id: int, status: string}
     */
    public function save(array $data): array
    {
        $material = $this->materials->save($data + ['status' => 'draft', 'guardian_visible' => false]);

        return ['material_id' => (int) $material->id, 'status' => (string) $material->status];
    }
}
