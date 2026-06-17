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

final class FamilyMessageSendRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'min:1'],
            'thread_id' => ['nullable', 'string', 'max:64'],
            'receiver_user_id' => ['nullable', 'integer', 'min:1'],
            'content' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required' => 'student_id is required',
            'content.required' => 'content is required',
        ];
    }
}
