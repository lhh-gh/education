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

namespace App\Http\Admin\Request\Education\Content;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class LearningMaterialSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'material_id' => ['nullable', 'integer', 'min:1'],
            'material_code' => ['required', 'string', 'max:64'],
            'material_name' => ['required', 'string', 'max:160'],
            'course_id' => ['nullable', 'integer', 'min:1'],
            'material_type' => ['required', 'string', 'max:40'],
            'guardian_visible' => ['nullable', 'boolean'],
            'summary' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return ['material_code.required' => 'material_code is required'];
    }
}
