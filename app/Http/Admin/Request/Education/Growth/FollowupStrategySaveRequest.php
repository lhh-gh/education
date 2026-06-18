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

namespace App\Http\Admin\Request\Education\Growth;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class FollowupStrategySaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'strategy_code' => ['required', 'string', 'max:64'],
            'strategy_name' => ['required', 'string', 'max:120'],
            'lead_stage' => ['required', 'string', 'max:40'],
            'score_level' => ['nullable', 'string', 'max:20'],
            'suggestion_template' => ['required', 'string'],
            'next_follow_hours' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return ['strategy_code.required' => 'strategy_code is required'];
    }
}
