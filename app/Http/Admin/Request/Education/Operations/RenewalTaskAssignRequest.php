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

final class RenewalTaskAssignRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'renewal_alert_id' => ['nullable', 'integer', 'min:1'],
            'assignee_id' => ['required', 'integer', 'min:1'],
            'next_follow_at' => ['nullable', 'date_format:Y-m-d H:i:s'],
        ];
    }
}
