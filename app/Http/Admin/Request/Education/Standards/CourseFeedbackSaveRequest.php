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

namespace App\Http\Admin\Request\Education\Standards;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class CourseFeedbackSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', 'min:1'],
            'standard_version_id' => ['nullable', 'integer', 'min:1'],
            'feedback_type' => ['required', 'string', 'max:40'],
            'score' => ['nullable', 'integer', 'min:0'],
            'content' => ['required', 'string'],
            'source_type' => ['nullable', 'string', 'max:40'],
            'source_id' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
