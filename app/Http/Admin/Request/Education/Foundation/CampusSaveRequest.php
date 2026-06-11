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
use App\Schema\Education\Foundation\CampusSchema;
use Hyperf\Validation\Request\FormRequest;

#[\Mine\Swagger\Attributes\FormRequest(
    schema: CampusSchema::class,
    only: ['name', 'code', 'contact_name', 'contact_phone', 'address', 'status', 'settings']
)]
class CampusSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:120',
            'code' => ['required', 'string', 'max:64', 'regex:/^[a-z][a-z0-9_\-]{1,63}$/'],
            'contact_name' => 'sometimes|nullable|string|max:60',
            'contact_phone' => 'sometimes|nullable|string|max:30',
            'address' => 'sometimes|nullable|string|max:255',
            'status' => 'sometimes|in:enabled,disabled',
            'settings' => 'sometimes|nullable|array',
        ];
    }
}
