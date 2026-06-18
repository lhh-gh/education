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

namespace App\Http\Admin\Request\Education\Family;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class HomeworkAssignmentSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'campus_id' => ['nullable', 'integer', 'min:1'],
            'title' => ['required', 'string', 'max:160'],
            'content' => ['required', 'string'],
            'course_id' => ['nullable', 'integer', 'min:1'],
            'class_id' => ['nullable', 'integer', 'min:1'],
            'lesson_id' => ['nullable', 'integer', 'min:1'],
            'due_at' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'title is required',
            'content.required' => 'content is required',
            'student_ids.required' => 'student_ids is required',
            'student_ids.min' => 'student_ids is required',
            'due_at.date_format' => 'due_at must use Y-m-d H:i:s',
        ];
    }
}
