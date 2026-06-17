<?php

declare(strict_types=1);

namespace App\Http\Api\Request\Education\Operations;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class GuardianMakeupPageRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'min:1'],
            'status' => ['nullable', 'in:available,used,expired,cancelled,arranged,completed'],
            'page' => ['nullable', 'integer', 'min:1'],
            'pageSize' => ['nullable', 'integer', 'between:1,100'],
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required' => 'student_id is required',
        ];
    }
}
