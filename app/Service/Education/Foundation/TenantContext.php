<?php

declare(strict_types=1);

namespace App\Service\Education\Foundation;

use App\Contract\Education\Foundation\TenantContextInterface;
use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use Hyperf\HttpServer\Contract\RequestInterface;

final class TenantContext implements TenantContextInterface
{
    public function __construct(
        private readonly RequestInterface $request
    ) {
    }

    public function id(): int
    {
        $tenantId = (int) $this->request->header('X-Tenant-Id', 0);
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
