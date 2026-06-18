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

final class AiKnowledgeDocumentSaveRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'document_code' => ['required', 'string', 'max:64'],
            'title' => ['required', 'string', 'max:160'],
            'content' => ['required', 'string'],
            'scope_type' => ['required', 'string', 'max:40'],
            'scope_value_json' => ['nullable', 'array'],
            'status' => ['nullable', 'string', 'in:draft,published,disabled'],
        ];
    }
}
