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

final class ContractRenewalSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'campus_id' => ['nullable', 'integer', 'min:1'],
            'contract_id' => ['required', 'integer', 'min:1'],
            'renewal_type' => ['nullable', 'string', 'max:40'],
            'status' => ['nullable', 'string', 'max:20'],
            'due_date' => ['required', 'date_format:Y-m-d'],
            'handled_by' => ['nullable', 'integer', 'min:1'],
            'handled_at' => ['nullable', 'date'],
            'result' => ['nullable', 'string', 'max:500'],
        ];
    }
}
