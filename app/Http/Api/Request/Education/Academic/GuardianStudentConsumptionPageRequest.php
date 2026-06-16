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

namespace App\Http\Api\Request\Education\Academic;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class GuardianStudentConsumptionPageRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'pageSize' => ['nullable', 'integer', 'min:1', 'max:100'],
            'account_id' => ['nullable', 'integer', 'min:1'],
            'source_type' => ['nullable', 'in:attendance,rollback'],
            'status' => ['nullable', 'in:active,reversed'],
            'start_at' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'end_at' => ['nullable', 'date_format:Y-m-d H:i:s', 'after:start_at'],
        ];
    }

    public function messages(): array
    {
        return [
            'source_type.in' => 'source_type has an invalid value',
            'status.in' => 'status has an invalid value',
            'end_at.after' => 'end_at must be after start_at',
        ];
    }
}
