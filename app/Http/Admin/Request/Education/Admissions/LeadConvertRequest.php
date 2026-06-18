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

final class LeadConvertRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'student_name' => ['required', 'string', 'max:120'],
            'guardian_name' => ['required', 'string', 'max:120'],
            'guardian_mobile' => ['nullable', 'string', 'max:30'],
            'guardian_relation' => ['nullable', 'string', 'max:30'],
            'lesson_package_id' => ['required', 'integer', 'min:1'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'enrolled_at' => ['nullable', 'date'],
            'remark' => ['nullable', 'string', 'max:500'],
        ];
    }
}
