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

use App\Repository\Education\Standards\ServiceTemplateRepository;

final class ServiceTemplateService
{
    public function __construct(private readonly ServiceTemplateRepository $templates) {}

    /**
     * @param array<string, mixed> $data
     * @return array{template_set_id: int, status: string}
     */
    public function saveSet(array $data): array
    {
        $set = $this->templates->saveSet($data + ['version_no' => 1, 'status' => 'draft']);

        return ['template_set_id' => (int) $set->id, 'status' => $this->statusValue($set->status)];
    }

    /**
     * @param list<array<string, mixed>> $items
     */
    public function saveItems(int $tenantId, int $campusId, int $setId, array $items): void
    {
        $this->templates->replaceItems($tenantId, $campusId, $setId, $items);
    }

    private function statusValue(mixed $status): string
    {
        return $status instanceof \BackedEnum ? (string) $status->value : (string) $status;
    }
}
