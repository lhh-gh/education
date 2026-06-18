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

use App\Repository\Education\Standards\DeliveryStandardRepository;

final class DeliveryStandardService
{
    public function __construct(private readonly DeliveryStandardRepository $standards) {}

    /**
     * @param array<string, mixed> $data
     * @return array{delivery_standard_id: int, status: string}
     */
    public function save(array $data): array
    {
        $standard = $this->standards->save($data + ['version_no' => 1, 'status' => 'draft']);

        return ['delivery_standard_id' => (int) $standard->id, 'status' => $this->statusValue($standard->status)];
    }

    private function statusValue(mixed $status): string
    {
        return $status instanceof \BackedEnum ? (string) $status->value : (string) $status;
    }
}
