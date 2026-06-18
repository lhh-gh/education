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

final class TrialStandardSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', 'min:1'],
            'standard_code' => ['required', 'string', 'max:64'],
            'standard_name' => ['required', 'string', 'max:120'],
            'guardian_visible' => ['nullable', 'boolean'],
            'items' => ['nullable', 'array'],
            'items.*.item_name' => ['required_with:items', 'string', 'max:120'],
            'items.*.item_content' => ['required_with:items', 'string'],
            'items.*.score_weight' => ['nullable', 'numeric'],
            'items.*.sort_order' => ['nullable', 'integer'],
        ];
    }
}
