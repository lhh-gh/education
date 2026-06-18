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

use App\Model\Education\Content\EducationStudentWork;

final class StudentWorkRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function save(array $data): EducationStudentWork
    {
        if (isset($data['id'])) {
            $work = EducationStudentWork::query()->where('tenant_id', $data['tenant_id'])->findOrFail($data['id']);
            $work->fill($data);
            $work->save();

            return $work;
        }

        return EducationStudentWork::query()->create($data);
    }

    public function findInTenant(int $tenantId, int $id): EducationStudentWork
    {
        return EducationStudentWork::query()->where('tenant_id', $tenantId)->findOrFail($id);
    }
}
