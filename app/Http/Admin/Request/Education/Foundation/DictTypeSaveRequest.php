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
use App\Schema\Education\Foundation\DictTypeSchema;
use Hyperf\Validation\Request\FormRequest;

#[\Mine\Swagger\Attributes\FormRequest(
    schema: DictTypeSchema::class,
    only: ['owner_type', 'tenant_id', 'code', 'name', 'description', 'status', 'is_locked', 'sort_order']
)]
class DictTypeSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'owner_type' => 'required|in:system,tenant',
            'tenant_id' => 'sometimes|nullable|integer|min:1',
            'code' => ['required', 'string', 'max:80', 'regex:/^[a-z][a-z0-9_\.\-]{1,79}$/'],
            'name' => 'required|string|max:120',
            'description' => 'sometimes|nullable|string|max:255',
            'status' => 'sometimes|in:enabled,disabled',
            'is_locked' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer|min:0|max:999999',
        ];
    }
}
