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

final class LearningReportSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer', 'min:1'],
            'campus_id' => ['nullable', 'integer', 'min:1'],
            'student_id' => ['required', 'integer', 'min:1'],
            'report_title' => ['required', 'string', 'max:160'],
            'report_period' => ['required', 'string', 'max:60'],
            'summary' => ['nullable', 'string'],
            'items' => ['nullable', 'array'],
            'items.*.item_type' => ['required_with:items', 'string', 'max:40'],
            'items.*.title' => ['required_with:items', 'string', 'max:160'],
            'items.*.content' => ['required_with:items', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required' => 'student_id is required',
            'report_title.required' => 'report_title is required',
            'report_period.required' => 'report_period is required',
        ];
    }
}
