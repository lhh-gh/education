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

namespace App\Http\Admin\Controller\Education\Payroll;

use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\Education\Foundation\ResolveEducationContextMiddleware;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Education\Payroll\WorkloadDisputeReviewRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Payroll\WorkloadDisputeService;
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
final class WorkloadDisputeController extends AbstractController
{
    use PayrollControllerTrait;

    public function __construct(private readonly WorkloadDisputeService $service, private readonly EventDispatcherInterface $events) {}

    #[Get(path: '/admin/education/payroll/workload-disputes/page', operationId: 'educationPayrollWorkloadDisputePage', summary: 'Payroll dispute page', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Payroll'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:payroll:dispute:page')]
    public function page(): Result
    {
        return $this->success($this->service->page($this->request->all(), $this->context()));
    }

    #[Post(path: '/admin/education/payroll/workload-disputes/{id}/review', operationId: 'educationPayrollWorkloadDisputeReview', summary: 'Payroll dispute review', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Payroll'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:payroll:dispute:review')]
    public function review(int $id, WorkloadDisputeReviewRequest $request): Result
    {
        $context = $this->context();
        $result = $this->service->review($id, $request->validated(), $context);
        $this->audit($this->events, 'education.payroll.dispute.' . $result['status'], 'workload_dispute', $result['dispute_id'], $context, $result);

        return $this->success($result);
    }
}
