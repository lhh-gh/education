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

use App\Model\Education\Academic\EducationStudentCourseAccount;
use Carbon\CarbonInterface;
use Hyperf\Swagger\Annotation\Schema;

#[Schema(title: 'EducationStudentCourseAccountSchema')]
final class StudentCourseAccountSchema implements \JsonSerializable
{
    public function __construct(private readonly EducationStudentCourseAccount $model) {}

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->model->id,
            'tenant_id' => $this->model->tenant_id,
            'campus_id' => $this->model->campus_id,
            'student_id' => $this->model->student_id,
            'student_name' => $this->model->student_name ?? null,
            'student_no' => $this->model->student_no ?? null,
            'course_id' => $this->model->course_id,
            'course_name' => $this->model->course_name ?? null,
            'purchased_units' => $this->model->purchased_units,
            'bonus_units' => $this->model->bonus_units,
            'consumed_units' => $this->model->consumed_units,
            'adjusted_units' => $this->model->adjusted_units,
            'refunded_units' => $this->model->refunded_units,
            'frozen_units' => $this->model->frozen_units,
            'available_units' => $this->model->available_units,
            'status' => $this->model->status,
            'first_enrollment_id' => $this->model->first_enrollment_id,
            'last_enrollment_id' => $this->model->last_enrollment_id,
            'opened_at' => $this->formatDate($this->model->opened_at),
            'expires_at' => $this->formatDate($this->model->expires_at),
            'remark' => $this->model->remark,
            'updated_at' => $this->formatDate($this->model->updated_at),
        ];
    }

    private function formatDate(mixed $value, string $format = CarbonInterface::DEFAULT_TO_STRING_FORMAT): ?string
    {
        return $value instanceof CarbonInterface ? $value->format($format) : ($value === null ? null : (string) $value);
    }
}
