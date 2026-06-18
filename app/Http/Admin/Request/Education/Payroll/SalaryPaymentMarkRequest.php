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

namespace App\Http\Admin\Request\Education\Payroll;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class SalaryPaymentMarkRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'salary_slip_id' => ['required', 'integer', 'min:1'],
            'payment_no' => ['required', 'string', 'max:64'],
            'paid_amount_cents' => ['required', 'integer', 'min:1'],
            'payment_method' => ['required', 'string', 'max:40'],
            'paid_at' => ['required', 'date_format:Y-m-d H:i:s'],
            'remark' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return ['payment_no.required' => 'payment_no is required'];
    }
}
