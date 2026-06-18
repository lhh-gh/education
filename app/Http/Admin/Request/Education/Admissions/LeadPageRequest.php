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

final class LeadPageRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'pageSize' => ['nullable', 'integer', 'min:1', 'max:100'],
            'campus_id' => ['nullable', 'integer', 'min:1'],
            'source_id' => ['nullable', 'integer', 'min:1'],
            'stage' => ['nullable', 'string', 'max:30'],
            'status' => ['nullable', 'string', 'max:20'],
            'owner_user_id' => ['nullable', 'integer', 'min:1'],
            'keyword' => ['nullable', 'string', 'max:120'],
            'next_follow_start' => ['nullable', 'date'],
            'next_follow_end' => ['nullable', 'date'],
        ];
    }
}
