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

class BatchLessonScheduleRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'class_id' => 'required|integer|min:1',
            'teacher_id' => 'sometimes|integer|min:1',
            'classroom_id' => 'sometimes|nullable|integer|min:1',
            'title' => 'sometimes|string|max:160',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'weekdays' => 'required|array|min:1',
            'weekdays.*' => 'integer|between:1,7',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'lesson_units' => 'sometimes|numeric|min:0.01',
            'remark' => 'sometimes|nullable|string|max:500',
        ];
    }
}
