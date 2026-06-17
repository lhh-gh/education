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

namespace App\Http\Admin\Request\Education\Admissions;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class LeadSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'campus_id' => ['required', 'integer', 'min:1'],
            'source_id' => ['nullable', 'integer', 'min:1'],
            'contact_name' => ['required', 'string', 'max:120'],
            'contact_mobile' => ['required', 'string', 'max:30'],
            'contact_wechat' => ['nullable', 'string', 'max:80'],
            'owner_user_id' => ['nullable', 'integer', 'min:1'],
            'intention_course_id' => ['nullable', 'integer', 'min:1'],
            'intention_level' => ['nullable', 'in:low,medium,high'],
            'next_follow_at' => ['nullable', 'date'],
            'remark' => ['nullable', 'string', 'max:500'],
            'lead_guardians' => ['nullable', 'array'],
            'lead_students' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'contact_mobile.required' => 'contact_mobile is required',
        ];
    }
}
