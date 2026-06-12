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

#[\Mine\Swagger\Attributes\FormRequest(schema: DictItemSchema::class)]
class DictItemPageRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'page' => 'sometimes|integer|min:1',
            'page_size' => 'sometimes|integer|min:1|max:200',
            'dict_type_id' => 'sometimes|integer|min:1',
            'dict_code' => 'sometimes|string|max:80',
            'keyword' => 'sometimes|string|max:120',
            'status' => 'sometimes|in:enabled,disabled',
        ];
    }
}
