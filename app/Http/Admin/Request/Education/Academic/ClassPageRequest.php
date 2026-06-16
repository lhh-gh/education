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
use App\Schema\Education\Academic\ClassSchema;
use Hyperf\Validation\Request\FormRequest;

#[\Mine\Swagger\Attributes\FormRequest(schema: ClassSchema::class)]
class ClassPageRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'page' => 'sometimes|integer|min:1',
            'pageSize' => 'sometimes|integer|between:1,100',
            'tenant_id' => 'sometimes|nullable|integer|min:1',
            'campus_id' => 'sometimes|nullable|integer|min:1',
            'course_id' => 'sometimes|nullable|integer|min:1',
            'main_teacher_id' => 'sometimes|nullable|integer|min:1',
            'classroom_id' => 'sometimes|nullable|integer|min:1',
            'keyword' => 'sometimes|nullable|string|max:120',
            'status' => 'sometimes|nullable|in:enabled,disabled',
            'class_type' => 'sometimes|nullable|in:group,one_to_one',
        ];
    }
}
