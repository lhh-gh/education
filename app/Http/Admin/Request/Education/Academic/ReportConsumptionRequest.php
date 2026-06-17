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

final class ReportConsumptionRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'page' => ['required', 'integer', 'min:1'],
            'pageSize' => ['required', 'integer', 'between:1,100'],
            'campus_id' => ['nullable', 'integer', 'min:1'],
            'course_id' => ['nullable', 'integer', 'min:1'],
            'class_id' => ['nullable', 'integer', 'min:1'],
            'student_id' => ['nullable', 'integer', 'min:1'],
            'account_id' => ['nullable', 'integer', 'min:1'],
            'source_type' => ['nullable', 'in:attendance,rollback'],
            'status' => ['nullable', 'in:active,reversed'],
            'start_at' => ['required', 'date_format:Y-m-d H:i:s'],
            'end_at' => ['required', 'date_format:Y-m-d H:i:s', 'after:start_at'],
            'group_by' => ['nullable', 'in:date,campus,course,class,teacher,source_type,status'],
        ];
    }

    public function messages(): array
    {
        return [
            'page.required' => 'page is required',
            'pageSize.between' => 'pageSize must be between 1 and 100',
            'campus_id.integer' => 'campus_id must be an integer',
            'start_at.required' => 'start_at is required',
            'start_at.date_format' => 'start_at must use Y-m-d H:i:s',
            'end_at.required' => 'end_at is required',
            'end_at.after' => 'end_at must be after start_at',
            'source_type.in' => 'source_type has an invalid value',
            'status.in' => 'status has an invalid value',
            'group_by.in' => 'group_by has an invalid value',
        ];
    }
}
