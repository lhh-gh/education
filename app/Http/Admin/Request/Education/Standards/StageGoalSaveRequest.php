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

final class StageGoalSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'service_package_id' => ['required', 'integer', 'min:1'],
            'goal_code' => ['required', 'string', 'max:64'],
            'goal_name' => ['required', 'string', 'max:120'],
            'goal_content' => ['required', 'string'],
            'sort_order' => ['nullable', 'integer'],
            'ability_point_ids' => ['nullable', 'array'],
            'ability_point_ids.*' => ['integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return ['goal_name.required' => 'goal_name is required'];
    }
}
