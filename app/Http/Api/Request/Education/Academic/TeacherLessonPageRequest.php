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

namespace App\Http\Api\Request\Education\Academic;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class TeacherLessonPageRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'pageSize' => ['nullable', 'integer', 'min:1', 'max:100'],
            'campus_id' => ['nullable', 'integer', 'min:1'],
            'start_at' => ['required', 'date_format:Y-m-d H:i:s'],
            'end_at' => ['required', 'date_format:Y-m-d H:i:s', 'after:start_at'],
            'status' => ['nullable', 'in:scheduled,cancelled,completed'],
            'keyword' => ['nullable', 'string', 'max:120'],
        ];
    }

    public function messages(): array
    {
        return [
            'start_at.required' => 'start_at is required',
            'start_at.date_format' => 'start_at must use Y-m-d H:i:s',
            'end_at.required' => 'end_at is required',
            'end_at.after' => 'end_at must be after start_at',
            'status.in' => 'status has an invalid value',
            'keyword.max' => 'keyword must not exceed 120 characters',
        ];
    }
}
