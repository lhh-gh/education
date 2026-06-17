<?php

declare(strict_types=1);

namespace App\Http\Admin\Request\Education\Operations;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class MakeupEntitlementPageRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'page' => ['required', 'integer', 'min:1'],
            'pageSize' => ['required', 'integer', 'between:1,100'],
            'campus_id' => ['nullable', 'integer', 'min:1'],
            'student_id' => ['nullable', 'integer', 'min:1'],
            'course_id' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'in:available,used,expired,cancelled'],
        ];
    }
}
