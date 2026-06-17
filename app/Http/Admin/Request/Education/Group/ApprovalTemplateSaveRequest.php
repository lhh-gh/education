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

namespace App\Http\Admin\Request\Education\Group;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class ApprovalTemplateSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'template_code' => ['required', 'string', 'max:64'],
            'template_name' => ['required', 'string', 'max:120'],
            'business_type' => ['required', 'string', 'max:60'],
            'status' => ['nullable', 'in:enabled,disabled'],
            'version' => ['nullable', 'integer', 'min:1'],
            'config_json' => ['nullable', 'array'],
            'nodes' => ['required', 'array', 'min:1'],
            'nodes.*.node_code' => ['required', 'string', 'max:64'],
            'nodes.*.node_name' => ['required', 'string', 'max:120'],
            'nodes.*.sort_order' => ['nullable', 'integer', 'min:1'],
            'nodes.*.assignee_type' => ['nullable', 'string', 'max:40'],
            'nodes.*.assignee_user_id' => ['required', 'integer', 'min:1'],
        ];
    }
}
