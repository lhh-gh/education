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

namespace App\Service\Education\Foundation;

use App\Contract\Education\Foundation\TenantContextInterface;
use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use Hyperf\HttpServer\Contract\RequestInterface;

final class TenantContext implements TenantContextInterface
{
    public function __construct(
        private readonly RequestInterface $request
    ) {}

    public function id(): int
    {
        $tenantId = (int) $this->request->header('X-Tenant-Id');
        if ($tenantId <= 0) {
            throw new BusinessException(
                ResultCode::UNPROCESSABLE_ENTITY,
                'X-Tenant-Id header is required',
                ['header' => 'X-Tenant-Id']
            );
        }

        return $tenantId;
    }
}
