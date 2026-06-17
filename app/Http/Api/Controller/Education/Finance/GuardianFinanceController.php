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

namespace App\Http\Api\Controller\Education\Finance;

use App\Http\Api\Middleware\Education\Foundation\MobileEducationContextMiddleware;
use App\Http\Api\Request\Education\Finance\GuardianFinancePageRequest;
use App\Http\Common\Controller\AbstractController;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Finance\GuardianFinanceService;
use App\Service\Education\Foundation\MobileContextService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Mine\Swagger\Attributes\PageResponse;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: MobileEducationContextMiddleware::class, priority: 99)]
final class GuardianFinanceController extends AbstractController
{
    public function __construct(
        private readonly GuardianFinanceService $service,
        private readonly MobileContextService $mobileContext
    ) {}

    #[Get(path: '/mobile/education/finance/guardian/orders', operationId: 'educationMobileGuardianFinanceOrders', summary: 'Guardian finance orders', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Mobile Finance'])]
    #[PageResponse(instance: new Result())]
    public function orders(GuardianFinancePageRequest $request): Result
    {
        return $this->success($this->service->orders($request->validated(), $this->mobileContext->mobile()));
    }

    #[Get(path: '/mobile/education/finance/guardian/receipts', operationId: 'educationMobileGuardianFinanceReceipts', summary: 'Guardian finance receipts', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Mobile Finance'])]
    #[PageResponse(instance: new Result())]
    public function receipts(GuardianFinancePageRequest $request): Result
    {
        return $this->success($this->service->receipts($request->validated(), $this->mobileContext->mobile()));
    }

    #[Get(path: '/mobile/education/finance/guardian/refunds', operationId: 'educationMobileGuardianFinanceRefunds', summary: 'Guardian finance refunds', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Mobile Finance'])]
    #[PageResponse(instance: new Result())]
    public function refunds(GuardianFinancePageRequest $request): Result
    {
        return $this->success($this->service->refunds($request->validated(), $this->mobileContext->mobile()));
    }
}
