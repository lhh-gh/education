<?php

declare(strict_types=1);

namespace App\Http\Admin\Request\Education\Operations;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class LessonBatchChangeRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'lesson_ids' => ['required', 'array', 'min:1', 'max:100'],
            'lesson_ids.*' => ['integer', 'min:1'],
            'change_type' => ['required', 'in:suspend,cancel,replace_teacher,replace_classroom'],
            'reason' => ['required', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'lesson_ids.min' => 'lesson_ids must contain at least one item',
        ];
    }
}
