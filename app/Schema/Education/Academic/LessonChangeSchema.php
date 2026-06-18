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

use App\Model\Education\Academic\EducationLessonChangeRecord;
use Carbon\CarbonInterface;
use Hyperf\Swagger\Annotation\Property;
use Hyperf\Swagger\Annotation\Schema;

#[Schema(title: 'EducationLessonChangeSchema')]
final class LessonChangeSchema implements \JsonSerializable
{
    #[Property(property: 'id', title: 'ID', type: 'int')]
    public ?int $id;

    public function __construct(private readonly EducationLessonChangeRecord $model) {}

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->model->id,
            'tenant_id' => $this->model->tenant_id,
            'campus_id' => $this->model->campus_id,
            'change_no' => $this->model->change_no,
            'change_type' => $this->model->change_type,
            'status' => $this->model->status,
            'leave_request_id' => $this->model->leave_request_id,
            'source_lesson_id' => $this->model->source_lesson_id,
            'source_lesson_student_id' => $this->model->source_lesson_student_id,
            'target_lesson_id' => $this->model->target_lesson_id,
            'class_id' => $this->model->class_id,
            'course_id' => $this->model->course_id,
            'student_id' => $this->model->student_id,
            'account_id' => $this->model->account_id,
            'source_teacher_id' => $this->model->source_teacher_id,
            'target_teacher_id' => $this->model->target_teacher_id,
            'source_classroom_id' => $this->model->source_classroom_id,
            'target_classroom_id' => $this->model->target_classroom_id,
            'source_start_at' => $this->formatDate($this->model->source_start_at),
            'source_end_at' => $this->formatDate($this->model->source_end_at),
            'target_start_at' => $this->formatDate($this->model->target_start_at),
            'target_end_at' => $this->formatDate($this->model->target_end_at),
            'lesson_units' => $this->model->lesson_units,
            'reason' => $this->model->reason,
            'cancelled_at' => $this->formatDate($this->model->cancelled_at),
            'cancelled_by' => $this->model->cancelled_by,
            'cancel_reason' => $this->model->cancel_reason,
            'created_at' => $this->formatDate($this->model->created_at),
        ];
    }

    private function formatDate(mixed $value): ?string
    {
        return $value instanceof CarbonInterface ? $value->format(CarbonInterface::DEFAULT_TO_STRING_FORMAT) : ($value === null ? null : (string) $value);
    }
}
