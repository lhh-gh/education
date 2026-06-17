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

namespace App\Http\Admin\Request\Education\Group;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class DataPermissionSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'min:1'],
            'scope_code' => ['nullable', 'string', 'max:64'],
            'scope_name' => ['nullable', 'string', 'max:120'],
            'scope_type' => ['required', 'in:group_all,org_tree,campus_set,self'],
            'campus_ids' => ['nullable', 'array'],
            'campus_ids.*' => ['integer', 'min:1'],
            'org_unit_ids' => ['nullable', 'array'],
            'org_unit_ids.*' => ['integer', 'min:1'],
            'effective_start' => ['nullable', 'date_format:Y-m-d'],
            'effective_end' => ['nullable', 'date_format:Y-m-d'],
            'status' => ['nullable', 'in:enabled,disabled'],
        ];
    }

    public function messages(): array
    {
        return ['scope_type.in' => 'scope_type has an invalid value'];
    }
}
