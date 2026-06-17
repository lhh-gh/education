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

namespace App\Service\Education\Finance;

use App\Repository\Education\Finance\PaymentRecordRepository;
use App\Service\Education\Foundation\EducationUserContext;

final class PaymentRecordService
{
    public function __construct(
        private readonly PaymentRecordRepository $repository
    ) {}

    /**
     * @param array<string, mixed> $filters
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function page(array $filters, EducationUserContext $context): array
    {
        return $this->repository->page($filters, $context);
    }
}
