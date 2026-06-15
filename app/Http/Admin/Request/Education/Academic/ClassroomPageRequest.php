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

namespace App\Http\Admin\Request\Education\Academic;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

class ClassroomPageRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'page' => 'sometimes|integer|min:1',
            'pageSize' => 'sometimes|integer|between:1,100',
            'tenant_id' => 'sometimes|nullable|integer|min:1',
            'campus_id' => 'sometimes|nullable|integer|min:1',
            'keyword' => 'sometimes|nullable|string|max:120',
            'status' => 'sometimes|nullable|in:enabled,disabled',
        ];
    }

    public function messages(): array
    {
        return ['pageSize.between' => 'pageSize must be between 1 and 100'];
    }
}
