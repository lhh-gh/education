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
use App\Schema\Education\Foundation\FeatureFlagSchema;
use Hyperf\Validation\Request\FormRequest;

#[\Mine\Swagger\Attributes\FormRequest(
    schema: FeatureFlagSchema::class,
    only: ['owner_type', 'tenant_id', 'feature_code', 'feature_name', 'description', 'enabled', 'config', 'effective_from', 'effective_to', 'status', 'is_locked']
)]
class FeatureFlagSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'owner_type' => 'required|in:system,tenant',
            'tenant_id' => 'sometimes|nullable|integer|min:1',
            'feature_code' => ['required', 'string', 'max:120', 'regex:/^[a-z][a-z0-9_\.\-]{1,119}$/'],
            'feature_name' => 'required|string|max:120',
            'description' => 'sometimes|nullable|string|max:255',
            'enabled' => 'required|boolean',
            'config' => 'sometimes|nullable|array',
            'effective_from' => 'sometimes|nullable|date',
            'effective_to' => 'sometimes|nullable|date|after:effective_from',
            'status' => 'sometimes|in:enabled,disabled',
            'is_locked' => 'sometimes|boolean',
        ];
    }
}
