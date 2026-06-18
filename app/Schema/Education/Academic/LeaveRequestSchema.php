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

use App\Model\Education\Academic\EducationLeaveRequest;
use Carbon\CarbonInterface;
use Hyperf\Swagger\Annotation\Property;
use Hyperf\Swagger\Annotation\Schema;

#[Schema(title: 'EducationLeaveRequestSchema')]
final class LeaveRequestSchema implements \JsonSerializable
{
    #[Property(property: 'id', title: 'ID', type: 'int')]
    public ?int $id;

    public function __construct(private readonly EducationLeaveRequest $model) {}

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->model->id,
            'tenant_id' => $this->model->tenant_id,
            'campus_id' => $this->model->campus_id,
            'leave_no' => $this->model->leave_no,
            'source' => $this->model->source,
            'leave_type' => $this->model->leave_type,
            'lesson_id' => $this->model->lesson_id,
            'lesson_student_id' => $this->model->lesson_student_id,
            'class_id' => $this->model->class_id,
            'course_id' => $this->model->course_id,
            'student_id' => $this->model->student_id,
            'student_name' => $this->model->lessonStudent?->student_name_snapshot,
            'account_id' => $this->model->account_id,
            'guardian_id' => $this->model->guardian_id,
            'teacher_id' => $this->model->teacher_id,
            'reason' => $this->model->reason,
            'status' => $this->model->status,
            'requested_at' => $this->formatDate($this->model->requested_at),
            'reviewed_at' => $this->formatDate($this->model->reviewed_at),
            'reviewed_by' => $this->model->reviewed_by,
            'review_remark' => $this->model->review_remark,
            'cancelled_at' => $this->formatDate($this->model->cancelled_at),
            'cancelled_by' => $this->model->cancelled_by,
            'cancel_reason' => $this->model->cancel_reason,
            'makeup_required' => $this->model->makeup_required,
            'makeup_lesson_id' => $this->model->makeup_lesson_id,
            'remark' => $this->model->remark,
            'created_at' => $this->formatDate($this->model->created_at),
            'updated_at' => $this->formatDate($this->model->updated_at),
        ];
    }

    private function formatDate(mixed $value): ?string
    {
        return $value instanceof CarbonInterface ? $value->format(CarbonInterface::DEFAULT_TO_STRING_FORMAT) : ($value === null ? null : (string) $value);
    }
}
