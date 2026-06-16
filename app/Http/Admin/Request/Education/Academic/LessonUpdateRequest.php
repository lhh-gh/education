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
class LessonUpdateRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:160',
            'teacher_id' => 'sometimes|integer|min:1',
            'classroom_id' => 'sometimes|nullable|integer|min:1',
            'start_at' => 'sometimes|date',
            'end_at' => 'sometimes|date',
            'lesson_units' => 'sometimes|numeric|min:0.01',
            'remark' => 'sometimes|nullable|string|max:500',
        ];
    }
}
