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
use App\Schema\Education\Academic\GuardianSchema;
use Hyperf\Validation\Request\FormRequest;

#[\Mine\Swagger\Attributes\FormRequest(
    schema: GuardianSchema::class,
    only: ['tenant_id', 'name', 'mobile', 'gender', 'openid', 'unionid', 'status', 'remark']
)]
class GuardianSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'tenant_id' => 'sometimes|nullable|integer|min:1',
            'name' => 'required|string|max:120',
            'mobile' => 'required|string|max:30',
            'gender' => 'required|in:male,female,unknown',
            'openid' => 'sometimes|nullable|string|max:80',
            'unionid' => 'sometimes|nullable|string|max:80',
            'status' => 'required|in:enabled,disabled',
            'remark' => 'sometimes|nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'mobile.required' => 'mobile is required',
            'gender.in' => 'gender must be one of male, female, unknown',
            'status.in' => 'status must be one of enabled, disabled',
        ];
    }
}
