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

final class PromptTemplateSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'campus_id' => ['nullable', 'integer', 'min:1'],
            'template_code' => ['required', 'string', 'max:64'],
            'feature_code' => ['required', 'string', 'max:64'],
            'template_name' => ['required', 'string', 'max:120'],
            'version' => ['nullable', 'integer', 'min:1'],
            'system_prompt' => ['required', 'string'],
            'user_prompt' => ['required', 'string'],
            'status' => ['nullable', 'string', 'in:draft,published,disabled'],
        ];
    }

    public function messages(): array
    {
        return [
            'template_code.required' => 'template_code is required',
            'feature_code.required' => 'feature_code is required',
            'template_name.required' => 'template_name is required',
            'system_prompt.required' => 'system_prompt is required',
            'user_prompt.required' => 'user_prompt is required',
        ];
    }
}
