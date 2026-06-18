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

namespace App\Http\Admin\Request\Education\Ai;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class AiModelConfigSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer', 'min:1'],
            'campus_id' => ['nullable', 'integer', 'min:1'],
            'config_code' => ['required', 'string', 'max:64'],
            'provider' => ['required', 'string', 'max:60'],
            'model_name' => ['required', 'string', 'max:120'],
            'api_key' => ['nullable', 'string', 'max:1000'],
            'base_url' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'in:enabled,disabled'],
            'default_temperature' => ['nullable', 'numeric', 'min:0', 'max:2'],
            'daily_token_limit' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'config_code.required' => 'config_code is required',
            'provider.required' => 'provider is required',
            'model_name.required' => 'model_name is required',
        ];
    }
}
