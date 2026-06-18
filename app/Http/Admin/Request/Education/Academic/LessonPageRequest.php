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
use App\Schema\Education\Academic\LessonSchema;
use Hyperf\Validation\Request\FormRequest;

#[\Mine\Swagger\Attributes\FormRequest(schema: LessonSchema::class)]
class LessonPageRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'page' => 'sometimes|integer|min:1',
            'pageSize' => 'sometimes|integer|between:1,100',
            'tenant_id' => 'sometimes|nullable|integer|min:1',
            'campus_id' => 'sometimes|nullable|integer|min:1',
            'class_id' => 'sometimes|nullable|integer|min:1',
            'course_id' => 'sometimes|nullable|integer|min:1',
            'teacher_id' => 'sometimes|nullable|integer|min:1',
            'classroom_id' => 'sometimes|nullable|integer|min:1',
            'status' => 'sometimes|nullable|in:scheduled,cancelled,completed',
            'source_type' => 'sometimes|nullable|in:manual,batch',
            'start_at' => 'sometimes|nullable|date',
            'end_at' => 'sometimes|nullable|date',
        ];
    }
}
