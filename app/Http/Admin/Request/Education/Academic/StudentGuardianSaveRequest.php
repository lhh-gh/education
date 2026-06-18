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

class StudentGuardianSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'relations' => 'required|array|min:1',
            'relations.*.guardian_id' => 'required|integer|min:1',
            'relations.*.relation' => 'required|in:father,mother,grandfather,grandmother,guardian,other',
            'relations.*.is_primary' => 'sometimes|nullable|boolean',
            'relations.*.can_receive_notice' => 'sometimes|nullable|boolean',
            'relations.*.can_submit_leave' => 'sometimes|nullable|boolean',
            'relations.*.remark' => 'sometimes|nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'relations.required' => 'relations is required',
            'relations.*.guardian_id.required' => 'guardian_id is required',
        ];
    }
}
