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

namespace App\Http\Api\Request\Education\Foundation;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class MobileContextRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'campus_id' => ['nullable', 'integer', 'min:1'],
            'client_type' => ['nullable', 'in:wechat_service,wechat_miniprogram,h5'],
        ];
    }

    public function messages(): array
    {
        return [
            'campus_id.integer' => 'campus_id must be an integer',
            'campus_id.min' => 'campus_id must be at least 1',
            'client_type.in' => 'client_type must be one of wechat_service, wechat_miniprogram, h5',
        ];
    }

    protected function validationData(): array
    {
        $data = parent::validationData();
        if (($data['client_type'] ?? '') === '') {
            $clientType = $this->getHeaderLine('X-Client-Type');
            if ($clientType !== '') {
                $data['client_type'] = $clientType;
            }
        }

        return $data;
    }
}
