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

namespace App\Http\Admin\Request\Education\Content;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class ShowcaseSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'showcase_id' => ['nullable', 'integer', 'min:1'],
            'student_id' => ['required', 'integer', 'min:1'],
            'stage_goal_id' => ['nullable', 'integer', 'min:1'],
            'title' => ['required', 'string', 'max:160'],
            'summary' => ['nullable', 'string'],
            'items' => ['nullable', 'array'],
        ];
    }
}
