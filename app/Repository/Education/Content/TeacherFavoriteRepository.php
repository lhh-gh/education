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

namespace App\Repository\Education\Content;

use App\Model\Education\Content\EducationTeacherMaterialFavorite;

final class TeacherFavoriteRepository
{
    public function save(int $tenantId, ?int $campusId, int $teacherId, int $materialId): EducationTeacherMaterialFavorite
    {
        return EducationTeacherMaterialFavorite::query()->updateOrCreate([
            'tenant_id' => $tenantId,
            'teacher_id' => $teacherId,
            'material_id' => $materialId,
        ], [
            'campus_id' => $campusId,
            'favorited_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function delete(int $tenantId, int $teacherId, int $materialId): void
    {
        EducationTeacherMaterialFavorite::query()
            ->where('tenant_id', $tenantId)
            ->where('teacher_id', $teacherId)
            ->where('material_id', $materialId)
            ->delete();
    }
}
