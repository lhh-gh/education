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

class EnrollmentPageRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'page' => 'sometimes|integer|min:1',
            'pageSize' => 'sometimes|integer|between:1,100',
            'tenant_id' => 'sometimes|nullable|integer|min:1',
            'campus_id' => 'sometimes|nullable|integer|min:1',
            'student_id' => 'sometimes|nullable|integer|min:1',
            'course_id' => 'sometimes|nullable|integer|min:1',
            'status' => 'sometimes|nullable|in:pending,confirmed,cancelled',
            'keyword' => 'sometimes|nullable|string|max:120',
            'enrolled_at_start' => 'sometimes|nullable|date_format:Y-m-d',
            'enrolled_at_end' => 'sometimes|nullable|date_format:Y-m-d',
        ];
    }
}
