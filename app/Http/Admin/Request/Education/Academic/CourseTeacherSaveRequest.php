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

class CourseTeacherSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'teacher_ids' => 'required|array',
            'teacher_ids.*' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'teacher_ids.required' => 'teacher_ids is required',
            'teacher_ids.*.integer' => 'teacher id must be an integer',
        ];
    }
}
