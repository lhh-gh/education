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

class ReconciliationBatchCreateRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'channel_code' => ['required', 'string', 'max:64'],
            'business_date' => ['required', 'date_format:Y-m-d'],
            'rows' => ['nullable', 'array'],
            'rows.*.channel_trade_no' => ['required_with:rows', 'string', 'max:120'],
            'rows.*.amount_cents' => ['required_with:rows', 'integer', 'min:0'],
            'rows.*.trade_time' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'file_url' => ['nullable', 'string', 'max:255'],
        ];
    }
}
