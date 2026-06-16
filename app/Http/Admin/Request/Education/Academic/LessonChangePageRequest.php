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

class LessonChangePageRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'page' => ['required', 'integer', 'min:1'],
            'pageSize' => ['required', 'integer', 'between:1,100'],
            'tenant_id' => ['nullable', 'integer', 'min:1'],
            'campus_id' => ['nullable', 'integer', 'min:1'],
            'change_type' => ['nullable', 'in:makeup,reschedule'],
            'status' => ['nullable', 'in:confirmed,cancelled'],
            'source_lesson_id' => ['nullable', 'integer', 'min:1'],
            'target_lesson_id' => ['nullable', 'integer', 'min:1'],
            'student_id' => ['nullable', 'integer', 'min:1'],
            'keyword' => ['nullable', 'string', 'max:120'],
        ];
    }

    public function messages(): array
    {
        return [
            'page.required' => 'page is required',
            'pageSize.between' => 'pageSize must be between 1 and 100',
            'status.in' => 'status has an invalid value',
        ];
    }
}
