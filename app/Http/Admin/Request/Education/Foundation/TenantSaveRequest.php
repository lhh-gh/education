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
use App\Schema\Education\Foundation\TenantSchema;
use Hyperf\Validation\Request\FormRequest;

#[\Mine\Swagger\Attributes\FormRequest(
    schema: TenantSchema::class,
    only: ['name', 'code', 'short_name', 'contact_name', 'contact_phone', 'status', 'settings']
)]
class TenantSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:120',
            'code' => ['required', 'string', 'max:64', 'regex:/^[a-z][a-z0-9_\-]{1,63}$/'],
            'short_name' => 'sometimes|nullable|string|max:60',
            'contact_name' => 'sometimes|nullable|string|max:60',
            'contact_phone' => 'sometimes|nullable|string|max:30',
            'status' => 'sometimes|in:enabled,disabled',
            'settings' => 'sometimes|nullable|array',
        ];
    }
}
