<?php

declare(strict_types=1);

namespace App\Http\Admin\Request\Education\Operations;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class ConsumptionReviewActionRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'action' => ['nullable', 'in:approve,reject'],
            'review_note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
