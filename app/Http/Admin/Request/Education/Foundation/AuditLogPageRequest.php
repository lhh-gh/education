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
use App\Schema\Education\Foundation\AuditLogSchema;
use Hyperf\Validation\Request\FormRequest;

#[\Mine\Swagger\Attributes\FormRequest(schema: AuditLogSchema::class)]
class AuditLogPageRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'page' => ['required', 'integer', 'min:1'],
            'pageSize' => ['required', 'integer', 'between:1,100'],
            'tenant_id' => ['nullable', 'integer', 'min:1'],
            'campus_id' => ['nullable', 'integer', 'min:1'],
            'module' => ['nullable', 'string', 'max:60'],
            'resource' => ['nullable', 'string', 'max:80'],
            'action' => ['nullable', 'string', 'max:120'],
            'business_type' => ['nullable', 'string', 'max:80'],
            'business_id' => ['nullable', 'string', 'max:80'],
            'actor_user_id' => ['nullable', 'integer', 'min:1'],
            'actor_type' => ['nullable', 'in:admin,teacher,guardian,system'],
            'keyword' => ['nullable', 'string', 'max:120'],
            'start_at' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'end_at' => ['nullable', 'date_format:Y-m-d H:i:s', 'after_or_equal:start_at'],
        ];
    }

    public function messages(): array
    {
        return [
            'page.required' => 'page is required',
            'pageSize.between' => 'pageSize must be between 1 and 100',
            'actor_type.in' => 'actor_type must be one of admin, teacher, guardian, system',
            'end_at.after_or_equal' => 'end_at must be greater than or equal to start_at',
        ];
    }
}
