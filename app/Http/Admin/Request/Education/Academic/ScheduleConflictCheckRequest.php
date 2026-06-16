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

class ScheduleConflictCheckRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'class_id' => 'required|integer|min:1',
            'teacher_id' => 'sometimes|integer|min:1',
            'classroom_id' => 'sometimes|nullable|integer|min:1',
            'start_at' => 'required|date',
            'end_at' => 'required|date',
            'exclude_lesson_id' => 'sometimes|nullable|integer|min:1',
        ];
    }
}
