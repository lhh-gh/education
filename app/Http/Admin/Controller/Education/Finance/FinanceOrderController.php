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
use App\Http\Admin\Request\Education\Finance\FinanceOrderCreateRequest;
use App\Http\Admin\Request\Education\Finance\FinanceOrderPageRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Http\CurrentUser;
use App\Service\Education\Academic\EnrollmentService;
use App\Service\Education\Finance\FinanceOrderService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\Post;
use Mine\Access\Attribute\Permission;
use Mine\Swagger\Attributes\PageResponse;
use Mine\Swagger\Attributes\ResultResponse;
use Psr\EventDispatcher\EventDispatcherInterface;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: ResolveEducationContextMiddleware::class, priority: 98)]
#[Middleware(middleware: OperationMiddleware::class, priority: 97)]
final class FinanceOrderController extends AbstractController
{
    use FinanceControllerTrait;

    public function __construct(
        private readonly FinanceOrderService $service,
        private readonly EnrollmentService $enrollmentService,
        private readonly CurrentUser $currentUser,
        private readonly EventDispatcherInterface $events
    ) {}

    #[Get(path: '/admin/education/finance/orders/page', operationId: 'educationFinanceOrderPage', summary: 'Finance order page', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Finance'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:finance:order:page')]
    public function page(FinanceOrderPageRequest $request): Result
    {
        return $this->success($this->service->page($request->validated(), $this->context()));
    }

    #[Post(path: '/admin/education/finance/orders/from-enrollment', operationId: 'educationFinanceOrderFromEnrollment', summary: 'Finance order create', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Finance'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:finance:order:create')]
    public function fromEnrollment(FinanceOrderCreateRequest $request): Result
    {
        $context = $this->context();
        $data = $request->validated();
        $result = $this->service->createFromEnrollment((int) $data['enrollment_id'], $data, $context);
        $this->audit($this->events, 'education.finance.order.created', 'finance_order', $result['order_id'], $context, $result);

        return $this->success($result);
    }

    #[Post(path: '/admin/education/finance/orders', operationId: 'educationFinanceOrderCreate', summary: 'Finance order create', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Finance'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:finance:order:create')]
    public function create(FinanceOrderCreateRequest $request): Result
    {
        $context = $this->context();
        $data = $request->validated();
        if (! isset($data['enrollment_id'])) {
            $enrollmentResult = $this->enrollmentService->create($data, $context, $this->currentUser->id());
            $data['enrollment_id'] = (int) $enrollmentResult['enrollment']->id;
        }
        $result = $this->service->createFromEnrollment((int) $data['enrollment_id'], $data, $context);
        $this->audit($this->events, 'education.finance.order.created', 'finance_order', $result['order_id'], $context, $result);

        return $this->success($result);
    }
}
