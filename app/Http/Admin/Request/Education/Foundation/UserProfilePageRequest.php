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
use Hyperf\Validation\Request\FormRequest;

class UserProfilePageRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'page' => 'sometimes|integer|min:1',
            'page_size' => 'sometimes|integer|min:1|max:200',
            'tenant_id' => 'sometimes|nullable|integer|min:1',
            'keyword' => 'sometimes|string|max:120',
            'role_code' => 'sometimes|in:platform_super_admin,platform_operator,tenant_admin,principal,academic_staff,front_desk,teacher,finance,guardian',
            'status' => 'sometimes|in:enabled,disabled',
        ];
    }
}
