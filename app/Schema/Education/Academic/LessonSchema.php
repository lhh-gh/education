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

use App\Model\Education\Academic\EducationLesson;
use Carbon\CarbonInterface;
use Hyperf\Swagger\Annotation\Property;
use Hyperf\Swagger\Annotation\Schema;

#[Schema(title: 'EducationLessonSchema')]
final class LessonSchema implements \JsonSerializable
{
    #[Property(property: 'id', title: 'ID', type: 'int')]
    public ?int $id;

    public function __construct(private readonly EducationLesson $model) {}

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->model->id,
            'tenant_id' => $this->model->tenant_id,
            'campus_id' => $this->model->campus_id,
            'lesson_no' => $this->model->lesson_no,
            'class_id' => $this->model->class_id,
            'course_id' => $this->model->course_id,
            'teacher_id' => $this->model->teacher_id,
            'classroom_id' => $this->model->classroom_id,
            'title' => $this->model->title,
            'start_at' => $this->formatDate($this->model->start_at),
            'end_at' => $this->formatDate($this->model->end_at),
            'duration_minutes' => $this->model->duration_minutes,
            'lesson_units' => $this->model->lesson_units,
            'student_count' => $this->model->student_count,
            'status' => $this->model->status,
            'source_type' => $this->model->source_type,
            'schedule_batch_no' => $this->model->schedule_batch_no,
            'class_name_snapshot' => $this->model->class_name_snapshot,
            'course_name_snapshot' => $this->model->course_name_snapshot,
            'teacher_name_snapshot' => $this->model->teacher_name_snapshot,
            'classroom_name_snapshot' => $this->model->classroom_name_snapshot,
            'cancelled_at' => $this->formatDate($this->model->cancelled_at),
            'cancel_reason' => $this->model->cancel_reason,
            'remark' => $this->model->remark,
        ];
    }

    private function formatDate(mixed $value): ?string
    {
        return $value instanceof CarbonInterface ? $value->format(CarbonInterface::DEFAULT_TO_STRING_FORMAT) : ($value === null ? null : (string) $value);
    }
}
