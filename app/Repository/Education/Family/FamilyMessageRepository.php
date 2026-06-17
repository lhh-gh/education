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

namespace App\Repository\Education\Family;

use App\Model\Education\Family\EducationFamilyMessage;

final class FamilyMessageRepository
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): EducationFamilyMessage
    {
        return EducationFamilyMessage::query()->create($data);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function thread(int $tenantId, string $threadId, int $studentId): array
    {
        return EducationFamilyMessage::query()
            ->where('tenant_id', $tenantId)
            ->where('thread_id', $threadId)
            ->where('student_id', $studentId)
            ->orderBy('id')
            ->get()
            ->map(static fn (EducationFamilyMessage $row): array => $row->toArray())
            ->all();
    }
}
