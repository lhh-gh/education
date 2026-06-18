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

use App\Repository\Education\Standards\TrialStandardRepository;

final class TrialStandardService
{
    public function __construct(private readonly TrialStandardRepository $standards) {}

    /**
     * @param array<string, mixed> $data
     * @return array{trial_standard_id: int, status: string}
     */
    public function save(array $data): array
    {
        $standard = $this->standards->save($data + ['version_no' => 1, 'status' => 'draft', 'guardian_visible' => false]);

        return ['trial_standard_id' => (int) $standard->id, 'status' => $this->statusValue($standard->status)];
    }

    /**
     * @param list<array<string, mixed>> $items
     */
    public function saveItems(int $tenantId, int $campusId, int $standardId, array $items): void
    {
        $normalized = array_map(static fn (array $item): array => [
            'item_name' => $item['item_name'],
            'item_content' => $item['item_content'],
            'score_weight' => $item['score_weight'] ?? null,
            'sort_order' => $item['sort_order'] ?? 0,
        ], $items);

        $this->standards->replaceItems($tenantId, $campusId, $standardId, $normalized);
    }

    private function statusValue(mixed $status): string
    {
        return $status instanceof \BackedEnum ? (string) $status->value : (string) $status;
    }
}
