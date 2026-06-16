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

use App\Model\Education\Academic\EducationAccountAdjustment;
use Carbon\CarbonInterface;
use Hyperf\Swagger\Annotation\Schema;

#[Schema(title: 'EducationAccountAdjustmentSchema')]
final class AccountAdjustmentSchema implements \JsonSerializable
{
    public function __construct(private readonly EducationAccountAdjustment $model) {}

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->model->id,
            'tenant_id' => $this->model->tenant_id,
            'campus_id' => $this->model->campus_id,
            'adjustment_no' => $this->model->adjustment_no,
            'account_id' => $this->model->account_id,
            'student_id' => $this->model->student_id,
            'student_name' => $this->model->student_name ?? null,
            'course_id' => $this->model->course_id,
            'course_name' => $this->model->course_name ?? null,
            'adjustment_type' => $this->model->adjustment_type,
            'direction' => $this->model->direction,
            'units' => $this->model->units,
            'before_available_units' => $this->model->before_available_units,
            'after_available_units' => $this->model->after_available_units,
            'before_adjusted_units' => $this->model->before_adjusted_units,
            'after_adjusted_units' => $this->model->after_adjusted_units,
            'status' => $this->model->status,
            'original_adjustment_id' => $this->model->original_adjustment_id,
            'rolled_back_at' => $this->formatDate($this->model->rolled_back_at),
            'rolled_back_by' => $this->model->rolled_back_by,
            'reason' => $this->model->reason,
            'created_at' => $this->formatDate($this->model->created_at),
        ];
    }

    private function formatDate(mixed $value, string $format = CarbonInterface::DEFAULT_TO_STRING_FORMAT): ?string
    {
        return $value instanceof CarbonInterface ? $value->format($format) : ($value === null ? null : (string) $value);
    }
}
