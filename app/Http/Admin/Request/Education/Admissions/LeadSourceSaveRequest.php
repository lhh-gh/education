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

namespace App\Http\Admin\Request\Education\Admissions;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class LeadSourceSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'campus_id' => ['nullable', 'integer', 'min:1'],
            'code' => ['required', 'string', 'max:64'],
            'name' => ['required', 'string', 'max:120'],
            'channel_type' => ['required', 'string', 'max:40'],
            'default_consultant_id' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'in:enabled,disabled'],
            'sort_order' => ['nullable', 'integer'],
            'remark' => ['nullable', 'string', 'max:500'],
        ];
    }
}
