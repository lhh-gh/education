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

class SingleLessonScheduleRequest extends ScheduleConflictCheckRequest
{
    public function rules(): array
    {
        return parent::rules() + [
            'title' => 'sometimes|string|max:160',
            'lesson_units' => 'sometimes|numeric|min:0.01',
            'remark' => 'sometimes|nullable|string|max:500',
        ];
    }
}
