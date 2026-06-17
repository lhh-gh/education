<?php

declare(strict_types=1);

namespace App\Http\Admin\Request\Education\Operations;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class MakeupArrangeRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'makeup_entitlement_id' => ['nullable', 'integer', 'min:1'],
            'makeup_lesson_id' => ['required', 'integer', 'min:1'],
            'arranged_at' => ['required', 'date_format:Y-m-d H:i:s'],
        ];
    }

    public function messages(): array
    {
        return [
            'makeup_lesson_id.required' => 'makeup_lesson_id is required',
        ];
    }
}
