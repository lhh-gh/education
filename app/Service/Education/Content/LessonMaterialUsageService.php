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

use App\Repository\Education\Content\LessonMaterialUsageRepository;

final class LessonMaterialUsageService
{
    public function __construct(private readonly LessonMaterialUsageRepository $usages) {}

    /**
     * @param array<string, mixed> $data
     * @param list<int> $assignedLessonIds
     * @return array{lesson_material_usage_id: int}
     */
    public function createForTeacher(array $data, array $assignedLessonIds): array
    {
        if (! \in_array((int) $data['lesson_id'], $assignedLessonIds, true)) {
            throw new \RuntimeException('lesson is not assigned to current teacher', 403);
        }
        $usage = $this->usages->create($data + ['used_at' => date('Y-m-d H:i:s')]);

        return ['lesson_material_usage_id' => (int) $usage->id];
    }
}
