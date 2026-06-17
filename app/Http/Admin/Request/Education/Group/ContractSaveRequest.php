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

final class ContractSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer', 'min:1'],
            'campus_id' => ['nullable', 'integer', 'min:1'],
            'contract_no' => ['required', 'string', 'max:64'],
            'contract_type' => ['required', 'string', 'max:40'],
            'title' => ['required', 'string', 'max:160'],
            'counterparty_name' => ['required', 'string', 'max:160'],
            'amount_cents' => ['required', 'integer', 'min:0'],
            'status' => ['nullable', 'in:draft,reviewing,active,expired,terminated,archived'],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after:start_date'],
            'owner_user_id' => ['nullable', 'integer', 'min:1'],
            'risk_level' => ['nullable', 'in:normal,warning,high,critical'],
        ];
    }

    public function messages(): array
    {
        return [
            'contract_no.required' => 'contract_no is required',
            'end_date.after' => 'end_date must be after start_date',
        ];
    }
}
