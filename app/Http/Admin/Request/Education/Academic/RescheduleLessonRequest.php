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

namespace App\Http\Admin\Request\Education\Academic;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

class RescheduleLessonRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'source_lesson_id' => ['required', 'integer', 'min:1'],
            'teacher_id' => ['required', 'integer', 'min:1'],
            'classroom_id' => ['nullable', 'integer', 'min:1'],
            'title' => ['required', 'string', 'max:160'],
            'start_at' => ['required', 'date_format:Y-m-d H:i:s'],
            'end_at' => ['required', 'date_format:Y-m-d H:i:s'],
            'lesson_units' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'reason' => ['required', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'source_lesson_id.required' => 'source_lesson_id is required',
            'teacher_id.required' => 'teacher_id is required',
            'title.required' => 'title is required',
            'start_at.required' => 'start_at is required',
            'end_at.required' => 'end_at is required',
            'lesson_units.min' => 'lesson_units must be greater than 0',
            'reason.required' => 'reason is required',
        ];
    }
}
