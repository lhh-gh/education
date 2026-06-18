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

use App\Repository\Education\Content\TeacherFavoriteRepository;

final class TeacherFavoriteService
{
    public function __construct(private readonly TeacherFavoriteRepository $favorites) {}

    /**
     * @return array{material_id: int, favorited: bool}
     */
    public function toggle(int $tenantId, ?int $campusId, int $teacherId, int $materialId, bool $favorited): array
    {
        if ($favorited) {
            $this->favorites->save($tenantId, $campusId, $teacherId, $materialId);
        } else {
            $this->favorites->delete($tenantId, $teacherId, $materialId);
        }

        return ['material_id' => $materialId, 'favorited' => $favorited];
    }
}
