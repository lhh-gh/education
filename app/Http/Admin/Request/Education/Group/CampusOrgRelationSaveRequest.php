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

final class CampusOrgRelationSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'org_unit_id' => ['required', 'integer', 'min:1'],
            'campus_id' => ['required', 'integer', 'min:1'],
            'relation_type' => ['nullable', 'string', 'max:40'],
            'effective_start' => ['nullable', 'date_format:Y-m-d'],
            'effective_end' => ['nullable', 'date_format:Y-m-d'],
        ];
    }
}
