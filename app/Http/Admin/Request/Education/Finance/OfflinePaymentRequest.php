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

final class OfflinePaymentRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'integer', 'min:1'],
            'channel_code' => ['required', 'string', 'max:64'],
            'payment_no' => ['nullable', 'string', 'max:64'],
            'channel_trade_no' => ['nullable', 'string', 'max:120'],
            'amount_cents' => ['required', 'integer', 'min:1'],
            'channel_fee_cents' => ['nullable', 'integer', 'min:0'],
            'payer_name' => ['nullable', 'string', 'max:120'],
            'remark' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'order_id.required' => 'order_id is required',
            'amount_cents.min' => 'amount_cents must be a positive integer',
        ];
    }
}
