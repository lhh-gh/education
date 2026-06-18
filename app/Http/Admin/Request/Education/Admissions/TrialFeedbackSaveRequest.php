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

namespace App\Http\Admin\Request\Education\Admissions;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class TrialFeedbackSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'trial_lesson_id' => ['required', 'integer', 'min:1'],
            'feedback_type' => ['required', 'in:teacher,consultant'],
            'teacher_id' => ['nullable', 'integer', 'min:1'],
            'consultant_user_id' => ['nullable', 'integer', 'min:1'],
            'score' => ['nullable', 'integer', 'min:0', 'max:5'],
            'content' => ['required', 'string'],
            'recommend_course_id' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
