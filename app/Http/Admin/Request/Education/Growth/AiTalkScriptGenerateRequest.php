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

namespace App\Http\Admin\Request\Education\Growth;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class AiTalkScriptGenerateRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'lead_id' => ['required', 'integer', 'min:1'],
            'script_type' => ['required', 'string', 'max:60'],
            'goal' => ['required', 'string', 'max:200'],
            'generated_text' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'lead_id.required' => 'lead_id is required',
            'script_type.required' => 'script_type is required',
        ];
    }
}
