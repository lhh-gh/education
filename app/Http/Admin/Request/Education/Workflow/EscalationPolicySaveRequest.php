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

final class EscalationPolicySaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'policy_code' => ['required', 'string', 'max:64'],
            'task_type' => ['required', 'string', 'max:60'],
            'overdue_minutes' => ['required', 'integer', 'min:1'],
            'escalate_to_user_ids_json' => ['required', 'array', 'min:1'],
            'status' => ['nullable', 'string', 'max:20'],
        ];
    }
}
