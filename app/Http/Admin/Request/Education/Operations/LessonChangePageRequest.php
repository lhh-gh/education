<?php

declare(strict_types=1);

namespace App\Http\Admin\Request\Education\Operations;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class LessonChangePageRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'page' => ['required', 'integer', 'min:1'],
            'pageSize' => ['required', 'integer', 'between:1,100'],
            'campus_id' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'in:pending,approved,rejected,applied,cancelled'],
            'change_type' => ['nullable', 'in:reschedule,suspend,cancel,replace_teacher,replace_classroom,substitute_teacher'],
        ];
    }
}
