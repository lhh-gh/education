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

use App\Model\Education\Academic\EducationLessonConsumption;
use Carbon\CarbonInterface;
use Hyperf\Swagger\Annotation\Schema;

#[Schema(title: 'EducationConsumptionSchema')]
final class ConsumptionSchema implements \JsonSerializable
{
    public function __construct(private readonly EducationLessonConsumption $model) {}

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->model->id,
            'tenant_id' => $this->model->tenant_id,
            'campus_id' => $this->model->campus_id,
            'consumption_no' => $this->model->consumption_no,
            'account_id' => $this->model->account_id,
            'student_id' => $this->model->student_id,
            'student_name' => $this->model->student_name ?? null,
            'course_id' => $this->model->course_id,
            'course_name' => $this->model->course_name ?? null,
            'lesson_id' => $this->model->lesson_id,
            'lesson_no' => $this->model->lesson_no ?? null,
            'lesson_student_id' => $this->model->lesson_student_id,
            'attendance_id' => $this->model->attendance_id,
            'source_type' => $this->model->source_type,
            'direction' => $this->model->direction,
            'units' => $this->model->units,
            'before_available_units' => $this->model->before_available_units,
            'after_available_units' => $this->model->after_available_units,
            'before_consumed_units' => $this->model->before_consumed_units,
            'after_consumed_units' => $this->model->after_consumed_units,
            'status' => $this->model->status,
            'original_consumption_id' => $this->model->original_consumption_id,
            'reversed_at' => $this->formatDate($this->model->reversed_at),
            'reversed_by' => $this->model->reversed_by,
            'reason' => $this->model->reason,
            'created_at' => $this->formatDate($this->model->created_at),
        ];
    }

    private function formatDate(mixed $value, string $format = CarbonInterface::DEFAULT_TO_STRING_FORMAT): ?string
    {
        return $value instanceof CarbonInterface ? $value->format($format) : ($value === null ? null : (string) $value);
    }
}
