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

use App\Model\Education\Academic\EducationEnrollment;
use Carbon\CarbonInterface;
use Hyperf\Swagger\Annotation\Schema;

#[Schema(title: 'EducationEnrollmentSchema')]
final class EnrollmentSchema implements \JsonSerializable
{
    public function __construct(private readonly EducationEnrollment $model) {}

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->model->id,
            'tenant_id' => $this->model->tenant_id,
            'campus_id' => $this->model->campus_id,
            'enrollment_no' => $this->model->enrollment_no,
            'student_id' => $this->model->student_id,
            'student_name_snapshot' => $this->model->student_name_snapshot,
            'course_id' => $this->model->course_id,
            'course_name_snapshot' => $this->model->course_name_snapshot,
            'lesson_package_id' => $this->model->lesson_package_id,
            'package_name_snapshot' => $this->model->package_name_snapshot,
            'account_id' => $this->model->account_id,
            'package_lesson_units' => $this->model->package_lesson_units,
            'package_bonus_units' => $this->model->package_bonus_units,
            'total_units' => $this->model->total_units,
            'list_price' => $this->model->list_price,
            'deal_amount' => $this->model->deal_amount,
            'status' => $this->model->status,
            'enrolled_at' => $this->formatDate($this->model->enrolled_at),
            'confirmed_at' => $this->formatDate($this->model->confirmed_at),
            'materialized_at' => $this->formatDate($this->model->materialized_at),
            'cancelled_at' => $this->formatDate($this->model->cancelled_at),
            'cancel_reason' => $this->model->cancel_reason,
            'remark' => $this->model->remark,
            'created_at' => $this->formatDate($this->model->created_at),
        ];
    }

    private function formatDate(mixed $value, string $format = CarbonInterface::DEFAULT_TO_STRING_FORMAT): ?string
    {
        return $value instanceof CarbonInterface ? $value->format($format) : ($value === null ? null : (string) $value);
    }
}
