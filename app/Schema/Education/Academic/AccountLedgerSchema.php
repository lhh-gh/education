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

namespace App\Schema\Education\Academic;

use Hyperf\Swagger\Annotation\Schema;

#[Schema(title: 'EducationAccountLedgerSchema')]
final class AccountLedgerSchema implements \JsonSerializable
{
    public function __construct(private readonly array $row) {}

    public function jsonSerialize(): mixed
    {
        return [
            'source_type' => $this->row['source_type'] ?? null,
            'source_id' => $this->row['source_id'] ?? null,
            'source_no' => $this->row['source_no'] ?? null,
            'occurred_at' => $this->row['occurred_at'] ?? null,
            'direction' => $this->row['direction'] ?? null,
            'units' => $this->row['units'] ?? null,
            'before_available_units' => $this->row['before_available_units'] ?? null,
            'after_available_units' => $this->row['after_available_units'] ?? null,
            'operator_id' => $this->row['operator_id'] ?? null,
            'operator_name' => $this->row['operator_name'] ?? null,
            'remark' => $this->row['remark'] ?? null,
        ];
    }
}
