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

namespace App\Http\Admin\Request\Education\Workflow;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class WorkflowRuleSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'rule_code' => ['required', 'string', 'max:64'],
            'rule_name' => ['required', 'string', 'max:120'],
            'event_type' => ['required', 'string', 'max:80'],
            'priority' => ['nullable', 'integer'],
            'dedupe_window_minutes' => ['nullable', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:500'],
            'conditions' => ['nullable', 'array'],
            'conditions.*.condition_field' => ['required_with:conditions', 'string', 'max:120'],
            'conditions.*.operator' => ['required_with:conditions', 'string', 'max:30'],
            'conditions.*.condition_value_json' => ['required_with:conditions'],
            'actions' => ['required', 'array', 'min:1'],
            'actions.*.action_type' => ['required', 'string', 'max:40'],
            'actions.*.action_config_json' => ['required', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'rule_code.required' => 'rule_code is required',
            'rule_name.required' => 'rule_name is required',
            'event_type.required' => 'event_type is required',
            'actions.required' => 'actions is required',
        ];
    }
}
