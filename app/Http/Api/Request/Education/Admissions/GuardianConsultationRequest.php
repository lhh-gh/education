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

namespace App\Http\Api\Request\Education\Admissions;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class GuardianConsultationRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'contact_name' => ['required', 'string', 'max:120'],
            'contact_mobile' => ['required', 'string', 'max:30'],
            'student_name' => ['required', 'string', 'max:120'],
            'student_age' => ['nullable', 'integer', 'min:1', 'max:30'],
            'interested_course' => ['nullable', 'string', 'max:120'],
        ];
    }
}
