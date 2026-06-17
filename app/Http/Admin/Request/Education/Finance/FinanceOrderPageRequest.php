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

final class FinanceOrderPageRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'pageSize' => ['nullable', 'integer', 'min:1', 'max:100'],
            'tenant_id' => ['nullable', 'integer', 'min:1'],
            'campus_id' => ['nullable', 'integer', 'min:1'],
            'student_id' => ['nullable', 'integer', 'min:1'],
            'guardian_id' => ['nullable', 'integer', 'min:1'],
            'enrollment_id' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'in:pending,paying,paid,partial_refunded,refunded,cancelled,closed'],
            'order_type' => ['nullable', 'string', 'max:40'],
            'keyword' => ['nullable', 'string', 'max:120'],
        ];
    }
}
