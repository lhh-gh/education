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

final class LeadLossRecordSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'loss_reason_id' => ['required', 'integer', 'min:1'],
            'detail' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return ['loss_reason_id.required' => 'loss_reason_id is required'];
    }
}
