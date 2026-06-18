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

namespace App\Http\Admin\Request\Education\Standards;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class ServiceTemplateSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'template_set_code' => ['required', 'string', 'max:64'],
            'template_set_name' => ['required', 'string', 'max:120'],
            'course_id' => ['nullable', 'integer', 'min:1'],
            'items' => ['nullable', 'array'],
            'items.*.item_type' => ['required_with:items', 'string', 'max:40'],
            'items.*.item_title' => ['required_with:items', 'string', 'max:120'],
            'items.*.item_content' => ['required_with:items', 'string'],
            'items.*.sort_order' => ['nullable', 'integer'],
        ];
    }
}
