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
use App\Schema\Education\Foundation\DictItemSchema;
use Hyperf\Validation\Request\FormRequest;

#[\Mine\Swagger\Attributes\FormRequest(
    schema: DictItemSchema::class,
    only: ['dict_type_id', 'label', 'value', 'color', 'extra', 'sort_order', 'status', 'is_default']
)]
class DictItemSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'dict_type_id' => 'required|integer|min:1',
            'label' => 'required|string|max:120',
            'value' => ['required', 'string', 'max:120', 'regex:/^[a-zA-Z0-9_\.\-]+$/'],
            'color' => 'sometimes|nullable|string|max:40',
            'extra' => 'sometimes|nullable|array',
            'sort_order' => 'sometimes|integer|min:0|max:999999',
            'status' => 'sometimes|in:enabled,disabled',
            'is_default' => 'sometimes|boolean',
        ];
    }
}
