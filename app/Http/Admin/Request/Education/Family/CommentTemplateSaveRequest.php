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

namespace App\Http\Admin\Request\Education\Family;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class CommentTemplateSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer', 'min:1'],
            'template_code' => ['required', 'string', 'max:64'],
            'template_name' => ['required', 'string', 'max:120'],
            'content' => ['required', 'string'],
            'course_id' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'in:enabled,disabled'],
            'sort_order' => ['nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'template_code.required' => 'template_code is required',
            'template_name.required' => 'template_name is required',
            'content.required' => 'content is required',
        ];
    }
}
