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
use App\Http\Admin\Request\Education\Finance\RefundApprovalRequest;
use App\Http\Admin\Request\Education\Finance\RefundRequestCreateRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Finance\RefundService;
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
final class RefundController extends AbstractController
{
    use FinanceControllerTrait;

    public function __construct(
        private readonly RefundService $service,
        private readonly EventDispatcherInterface $events
    ) {}

    #[Post(path: '/admin/education/finance/refund-requests', operationId: 'educationFinanceRefundRequest', summary: 'Refund request', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Finance'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:finance:refund:create')]
    public function request(RefundRequestCreateRequest $request): Result
    {
        $context = $this->context();
        $result = $this->service->request($request->validated(), $context);
        $this->audit($this->events, 'education.finance.refund.requested', 'refund_request', $result['refund_request_id'], $context, $result);

        return $this->success($result);
    }

    #[Post(path: '/admin/education/finance/refund-requests/{id}/approve', operationId: 'educationFinanceRefundApprove', summary: 'Refund approve', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Finance'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:finance:refund:approve')]
    public function approve(int $id, RefundApprovalRequest $request): Result
    {
        $context = $this->context();
        $result = $this->service->approve($id, $request->validated(), $context);
        $this->audit($this->events, 'education.finance.refund.approved', 'refund_request', $result['refund_request_id'], $context, $result);

        return $this->success($result);
    }
}
