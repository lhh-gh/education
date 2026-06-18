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

use App\Repository\Education\Content\ContentMetricRepository;

final class MaterialReadService
{
    public function __construct(private readonly ContentMetricRepository $metrics) {}

    /**
     * @param array<string, mixed> $data
     * @return array{material_read_record_id: int}
     */
    public function markMaterialRead(array $data): array
    {
        $read = $this->metrics->saveMaterialRead($data);

        return ['material_read_record_id' => (int) $read->id];
    }

    /**
     * @param array<string, mixed> $data
     * @return array{showcase_read_record_id: int}
     */
    public function markShowcaseRead(array $data): array
    {
        $read = $this->metrics->saveShowcaseRead($data);

        return ['showcase_read_record_id' => (int) $read->id];
    }
}
