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

namespace App\Http\Admin\Request\Education\Workflow;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class WorkflowTaskCommentRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return ['content' => ['required', 'string', 'max:2000']];
    }

    public function messages(): array
    {
        return ['content.required' => 'content is required'];
    }
}
