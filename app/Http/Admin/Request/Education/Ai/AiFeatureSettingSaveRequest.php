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

final class AiFeatureSettingSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'campus_id' => ['nullable', 'integer', 'min:1'],
            'feature_code' => ['required', 'string', 'max:64'],
            'feature_name' => ['required', 'string', 'max:120'],
            'model_config_id' => ['required', 'integer', 'min:1'],
            'enabled' => ['nullable', 'boolean'],
            'review_required' => ['nullable', 'boolean'],
            'safety_level' => ['nullable', 'string', 'in:normal,warning,high,blocked'],
            'config_json' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'feature_code.required' => 'feature_code is required',
            'feature_name.required' => 'feature_name is required',
            'model_config_id.required' => 'model_config_id is required',
        ];
    }
}
