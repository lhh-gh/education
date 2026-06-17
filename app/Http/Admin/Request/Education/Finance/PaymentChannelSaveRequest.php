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

namespace App\Http\Admin\Request\Education\Finance;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class PaymentChannelSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'tenant_id' => ['nullable', 'integer', 'min:1'],
            'campus_id' => ['nullable', 'integer', 'min:1'],
            'channel_code' => ['required', 'string', 'max:64'],
            'channel_name' => ['required', 'string', 'max:120'],
            'channel_type' => ['required', 'in:offline_cash,offline_bank,offline_pos,wechat'],
            'config_json' => ['nullable', 'array'],
            'status' => ['nullable', 'in:enabled,disabled'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
