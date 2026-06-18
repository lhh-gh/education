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

use App\Model\Education\Academic\EducationLessonAttendance;
use Carbon\CarbonInterface;
use Hyperf\Swagger\Annotation\Schema;

#[Schema(title: 'EducationAttendanceSchema')]
final class AttendanceSchema implements \JsonSerializable
{
    public function __construct(private readonly EducationLessonAttendance $model) {}

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->model->id,
            'tenant_id' => $this->model->tenant_id,
            'campus_id' => $this->model->campus_id,
            'lesson_id' => $this->model->lesson_id,
            'lesson_student_id' => $this->model->lesson_student_id,
            'class_id' => $this->model->class_id,
            'course_id' => $this->model->course_id,
            'student_id' => $this->model->student_id,
            'student_name_snapshot' => $this->model->student_name_snapshot ?? null,
            'account_id' => $this->model->account_id,
            'attendance_status' => $this->model->attendance_status,
            'consume_policy' => $this->model->consume_policy,
            'planned_units' => $this->model->planned_units,
            'consumed_units' => $this->model->consumed_units,
            'consumption_status' => $this->model->consumption_status,
            'submitted_at' => $this->formatDate($this->model->submitted_at),
            'submitted_by' => $this->model->submitted_by,
            'attendance_batch_no' => $this->model->attendance_batch_no,
            'remark' => $this->model->remark,
        ];
    }

    private function formatDate(mixed $value, string $format = CarbonInterface::DEFAULT_TO_STRING_FORMAT): ?string
    {
        return $value instanceof CarbonInterface ? $value->format($format) : ($value === null ? null : (string) $value);
    }
}
