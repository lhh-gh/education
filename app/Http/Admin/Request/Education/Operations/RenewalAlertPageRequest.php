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

namespace App\Http\Admin\Request\Education\Operations;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class RenewalAlertPageRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'page' => ['required', 'integer', 'min:1'],
            'pageSize' => ['required', 'integer', 'between:1,100'],
            'campus_id' => ['nullable', 'integer', 'min:1'],
            'alert_type' => ['nullable', 'in:low_balance,expire_soon,expired'],
            'alert_level' => ['nullable', 'in:normal,warning,urgent'],
            'status' => ['nullable', 'in:open,converted,ignored,closed'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'status has an invalid value',
        ];
    }
}
