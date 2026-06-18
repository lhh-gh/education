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

namespace App\Http\Admin\Request\Education\Standards;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class ServicePackageSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'service_package_id' => ['nullable', 'integer', 'min:1'],
            'package_code' => ['required', 'string', 'max:64'],
            'package_name' => ['required', 'string', 'max:120'],
            'course_id' => ['required', 'integer', 'min:1'],
            'guardian_visible' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return ['package_code.required' => 'package_code is required'];
    }
}
