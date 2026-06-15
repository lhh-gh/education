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

namespace App\Http\Admin\Request\Education\Academic;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

class EnrollmentCancelRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return ['cancel_reason' => 'required|string|max:500'];
    }

    public function messages(): array
    {
        return ['cancel_reason.required' => 'cancel_reason is required'];
    }
}
