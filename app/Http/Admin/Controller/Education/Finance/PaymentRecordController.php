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

namespace App\Http\Admin\Controller\Education\Finance;

use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\Education\Foundation\ResolveEducationContextMiddleware;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Education\Finance\FinanceOrderPageRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Finance\PaymentRecordService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Mine\Access\Attribute\Permission;
use Mine\Swagger\Attributes\PageResponse;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: ResolveEducationContextMiddleware::class, priority: 98)]
final class PaymentRecordController extends AbstractController
{
    use FinanceControllerTrait;

    public function __construct(private readonly PaymentRecordService $service) {}

    #[Get(path: '/admin/education/finance/payment-records/page', operationId: 'educationFinancePaymentRecordPage', summary: 'Payment record page', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Finance'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:finance:payment:page')]
    public function page(FinanceOrderPageRequest $request): Result
    {
        return $this->success($this->service->page($request->validated(), $this->context()));
    }
}
