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

namespace App\Http\Admin\Request\Education\Foundation;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use App\Schema\Education\Foundation\UserProfileSchema;
use Hyperf\Validation\Request\FormRequest;

#[\Mine\Swagger\Attributes\FormRequest(
    schema: UserProfileSchema::class,
    only: ['tenant_id', 'user_id', 'role_code', 'display_name', 'mobile', 'avatar', 'openid', 'unionid', 'status', 'current_campus_id', 'settings']
)]
class UserProfileSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'tenant_id' => 'sometimes|nullable|integer|min:1',
            'user_id' => 'required|integer|min:1',
            'role_code' => 'required|in:platform_super_admin,platform_operator,tenant_admin,principal,academic_staff,front_desk,teacher,finance,guardian',
            'display_name' => 'required|string|max:80',
            'mobile' => 'sometimes|nullable|string|max:30',
            'avatar' => 'sometimes|nullable|string|max:255',
            'openid' => 'sometimes|nullable|string|max:80',
            'unionid' => 'sometimes|nullable|string|max:80',
            'status' => 'sometimes|in:enabled,disabled',
            'current_campus_id' => 'sometimes|nullable|integer|min:1',
            'settings' => 'sometimes|nullable|array',
        ];
    }
}
