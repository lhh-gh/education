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

final class ApprovalInstanceCreateRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'template_id' => ['required', 'integer', 'min:1'],
            'business_type' => ['required', 'string', 'max:60'],
            'business_id' => ['required', 'integer', 'min:1'],
            'payload_json' => ['nullable', 'array'],
        ];
    }
}
