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

namespace App\Http\Admin\Request\Education\Growth;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class ChannelCostSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'source_id' => ['required', 'integer', 'min:1'],
            'cost_date' => ['required', 'date'],
            'cost_type' => ['required', 'string', 'max:40'],
            'amount_cents' => ['required', 'integer', 'min:0'],
            'remark' => ['nullable', 'string', 'max:500'],
        ];
    }
}
