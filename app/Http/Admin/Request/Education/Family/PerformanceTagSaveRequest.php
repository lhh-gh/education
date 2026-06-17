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

final class PerformanceTagSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer', 'min:1'],
            'tag_code' => ['required', 'string', 'max:64'],
            'tag_name' => ['required', 'string', 'max:80'],
            'tag_type' => ['required', 'string', 'max:40'],
            'status' => ['nullable', 'in:enabled,disabled'],
            'sort_order' => ['nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'tag_code.required' => 'tag_code is required',
            'tag_name.required' => 'tag_name is required',
            'tag_type.required' => 'tag_type is required',
        ];
    }
}
