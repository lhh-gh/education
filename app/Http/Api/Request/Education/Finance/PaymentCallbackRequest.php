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

namespace App\Http\Api\Request\Education\Finance;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class PaymentCallbackRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'integer', 'min:1'],
            'payment_no' => ['required', 'string', 'max:64'],
            'channel_trade_no' => ['required', 'string', 'max:120'],
            'amount_cents' => ['required', 'integer', 'min:1'],
            'signature' => ['required', 'string', 'max:255'],
        ];
    }
}
