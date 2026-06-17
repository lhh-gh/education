<?php

declare(strict_types=1);

namespace App\Http\Admin\Request\Education\Operations;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class LessonChangeCreateRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'lesson_id' => ['required', 'integer', 'min:1'],
            'change_type' => ['required', 'in:reschedule,suspend,cancel,replace_teacher,replace_classroom,substitute_teacher'],
            'new_values_json' => ['required', 'array'],
            'reason' => ['required', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'change_type.in' => 'change_type has an invalid value',
            'reason.required' => 'reason is required',
        ];
    }
}
