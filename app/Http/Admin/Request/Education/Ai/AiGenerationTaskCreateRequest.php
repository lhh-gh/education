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

final class AiGenerationTaskCreateRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'feature_code' => ['required', 'string', 'max:64'],
            'business_type' => ['required', 'string', 'max:60'],
            'business_id' => ['nullable', 'integer', 'min:1'],
            'prompt_variables' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'feature_code.required' => 'feature_code is required',
            'business_type.required' => 'business_type is required',
        ];
    }
}
