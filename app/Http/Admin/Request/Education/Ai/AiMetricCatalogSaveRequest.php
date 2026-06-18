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

namespace App\Http\Admin\Request\Education\Ai;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class AiMetricCatalogSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'metric_code' => ['required', 'string', 'max:64'],
            'metric_name' => ['required', 'string', 'max:120'],
            'metric_group' => ['required', 'string', 'max:60'],
            'query_key' => ['required', 'string', 'max:120'],
            'allowed_roles_json' => ['required', 'array', 'min:1'],
            'allowed_roles_json.*' => ['string', 'max:40'],
            'status' => ['nullable', 'string', 'in:enabled,disabled'],
        ];
    }
}
