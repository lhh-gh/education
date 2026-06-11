<?php

declare(strict_types=1);

namespace App\Http\Admin\Request\Education\Foundation;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

class CampusPageRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'page' => 'sometimes|integer|min:1',
            'page_size' => 'sometimes|integer|min:1|max:200',
            'keyword' => 'sometimes|string|max:120',
            'status' => 'sometimes|in:enabled,disabled',
        ];
    }
}
