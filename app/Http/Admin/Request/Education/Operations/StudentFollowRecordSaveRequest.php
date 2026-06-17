<?php

declare(strict_types=1);

namespace App\Http\Admin\Request\Education\Operations;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class StudentFollowRecordSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'student_id' => ['nullable', 'integer', 'min:1'],
            'follow_type' => ['required', 'in:phone,wechat,offline,system'],
            'content' => ['required', 'string'],
            'next_follow_at' => ['nullable', 'date_format:Y-m-d H:i:s'],
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'content is required',
        ];
    }
}
