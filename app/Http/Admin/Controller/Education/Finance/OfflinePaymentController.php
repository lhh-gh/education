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
use App\Http\Admin\Request\Education\Finance\OfflinePaymentRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Finance\OfflineCollectionService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\Post;
use Mine\Access\Attribute\Permission;
use Mine\Swagger\Attributes\ResultResponse;
use Psr\EventDispatcher\EventDispatcherInterface;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: ResolveEducationContextMiddleware::class, priority: 98)]
#[Middleware(middleware: OperationMiddleware::class, priority: 97)]
final class OfflinePaymentController extends AbstractController
{
    use FinanceControllerTrait;

    public function __construct(
        private readonly OfflineCollectionService $service,
        private readonly EventDispatcherInterface $events
    ) {}

    #[Post(path: '/admin/education/finance/offline-payments', operationId: 'educationFinanceOfflinePayment', summary: 'Offline payment', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Finance'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:finance:payment:offline')]
    public function confirm(OfflinePaymentRequest $request): Result
    {
        $context = $this->context();
        $result = $this->service->confirm($request->validated(), $context);
        $this->audit($this->events, 'education.finance.payment.offline_confirmed', 'payment_record', $result['payment_record_id'], $context, $result);

        return $this->success($result);
    }
}
