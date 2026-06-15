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

class StudentCourseAccountStatusRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return ['status' => 'required|in:active,frozen,closed'];
    }

    public function messages(): array
    {
        return ['status.in' => 'status has an invalid value'];
    }
}
