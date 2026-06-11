<?php

declare(strict_types=1);

namespace App\Http\Admin\Request\Education\Foundation;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

class TenantStatusRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'status' => 'required|in:enabled,disabled',
        ];
    }
}
