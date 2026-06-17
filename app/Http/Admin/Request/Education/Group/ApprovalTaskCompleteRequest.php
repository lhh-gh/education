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

final class ApprovalTaskCompleteRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'result' => ['required', 'in:approved,rejected'],
            'comment' => ['nullable', 'string', 'max:500'],
            'override_permission' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return ['result.in' => 'result has an invalid value'];
    }
}
