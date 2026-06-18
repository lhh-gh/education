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
use App\Http\Admin\Request\Education\Payroll\SalaryBatchCalculateRequest;
use App\Http\Admin\Request\Education\Payroll\SalaryBatchPageRequest;
use App\Http\Admin\Request\Education\Payroll\SalaryReviewRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Payroll\SalaryBatchService;
use App\Service\Education\Payroll\SalaryCalculationService;
use App\Service\Education\Payroll\SalaryReviewService;
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
final class SalaryBatchController extends AbstractController
{
    use PayrollControllerTrait;

    public function __construct(
        private readonly SalaryBatchService $batchService,
        private readonly SalaryCalculationService $calculationService,
        private readonly SalaryReviewService $reviewService,
        private readonly EventDispatcherInterface $events
    ) {}

    #[Get(path: '/admin/education/payroll/salary-batches/page', operationId: 'educationPayrollSalaryBatchPage', summary: 'Payroll batch page', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Payroll'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:payroll:batch:page')]
    public function page(SalaryBatchPageRequest $request): Result
    {
        return $this->success($this->batchService->page($request->validated(), $this->context()));
    }

    #[Post(path: '/admin/education/payroll/salary-batches/calculate', operationId: 'educationPayrollSalaryBatchCalculate', summary: 'Payroll batch calculate', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Payroll'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:payroll:batch:calculate')]
    public function calculate(SalaryBatchCalculateRequest $request): Result
    {
        $context = $this->context();
        $result = $this->calculationService->calculate($request->validated(), $context);
        $this->audit($this->events, 'education.payroll.batch.calculated', 'salary_batch', $result['batch_id'], $context, $result);

        return $this->success($result);
    }

    #[Post(path: '/admin/education/payroll/salary-batches/{id}/rebuild', operationId: 'educationPayrollSalaryBatchRebuild', summary: 'Payroll batch rebuild', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Payroll'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:payroll:batch:calculate')]
    public function rebuild(int $id): Result
    {
        $context = $this->context();
        $result = $this->calculationService->rebuild($id, $context);
        $this->audit($this->events, 'education.payroll.batch.calculated', 'salary_batch', $result['batch_id'], $context, $result);

        return $this->success($result);
    }

    #[Post(path: '/admin/education/payroll/salary-batches/{id}/submit-review', operationId: 'educationPayrollSalaryBatchSubmit', summary: 'Payroll batch submit', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Payroll'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:payroll:batch:submit')]
    public function submitReview(int $id): Result
    {
        $context = $this->context();
        $result = $this->batchService->submitReview($id, $context);
        $this->audit($this->events, 'education.payroll.batch.submitted', 'salary_batch', $result['batch_id'], $context, $result);

        return $this->success($result);
    }

    #[Post(path: '/admin/education/payroll/salary-batches/{id}/approve', operationId: 'educationPayrollSalaryBatchApprove', summary: 'Payroll batch approve', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Payroll'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:payroll:batch:approve')]
    public function approve(int $id, SalaryReviewRequest $request): Result
    {
        $context = $this->context();
        $result = $this->reviewService->approveBatch($id, $request->validated(), $context);
        $this->audit($this->events, 'education.payroll.batch.approved', 'salary_batch', $result['batch_id'], $context, $result);

        return $this->success($result);
    }

    #[Post(path: '/admin/education/payroll/salary-batches/{id}/reject', operationId: 'educationPayrollSalaryBatchReject', summary: 'Payroll batch reject', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Payroll'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:payroll:batch:approve')]
    public function reject(int $id, SalaryReviewRequest $request): Result
    {
        $context = $this->context();
        $result = $this->reviewService->rejectBatch($id, $request->validated(), $context);
        $this->audit($this->events, 'education.payroll.batch.rejected', 'salary_batch', $result['batch_id'], $context, $result);

        return $this->success($result);
    }
}
