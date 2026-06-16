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

final class NoticeSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'campus_id' => ['nullable', 'integer', 'min:1'],
            'notice_type' => ['required', 'in:academic,activity,fee,system'],
            'target_type' => ['required', 'in:all,campus,class,student'],
            'target_id' => ['nullable', 'integer', 'min:1'],
            'title' => ['required', 'string', 'max:160'],
            'content' => ['required', 'string', 'max:5000'],
            'priority' => ['required', 'in:normal,important,urgent'],
            'expire_at' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'remark' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'title is required',
            'notice_type.in' => 'notice_type has an invalid value',
            'target_type.in' => 'target_type has an invalid value',
            'priority.in' => 'priority has an invalid value',
        ];
    }
}
