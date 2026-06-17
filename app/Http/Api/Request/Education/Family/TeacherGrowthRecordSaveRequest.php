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

namespace App\Http\Api\Request\Education\Family;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class TeacherGrowthRecordSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'min:1'],
            'record_type' => ['required', 'string', 'max:40'],
            'title' => ['required', 'string', 'max:160'],
            'content' => ['required', 'string'],
            'publish' => ['nullable', 'boolean'],
        ];
    }
}
