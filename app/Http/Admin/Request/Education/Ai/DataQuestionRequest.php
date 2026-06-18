<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */

namespace App\Http\Admin\Request\Education\Ai;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class DataQuestionRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'question_text' => ['required', 'string'],
            'metric_codes' => ['required', 'array', 'min:1'],
            'metric_codes.*' => ['string', 'max:64'],
            'date_range' => ['nullable', 'array', 'size:2'],
            'date_range.*' => ['date'],
        ];
    }

    public function messages(): array
    {
        return [
            'question_text.required' => 'question_text is required',
            'metric_codes.required' => 'metric_codes is required',
        ];
    }
}
