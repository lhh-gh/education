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
use App\Http\Admin\Request\Education\Payroll\SalaryAdjustmentRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Repository\Education\Payroll\SalarySlipRepository;
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
final class SalarySlipController extends AbstractController
{
    use PayrollControllerTrait;

    public function __construct(private readonly SalarySlipRepository $repository, private readonly SalaryReviewService $reviewService, private readonly EventDispatcherInterface $events) {}

    #[Get(path: '/admin/education/payroll/salary-slips/page', operationId: 'educationPayrollSalarySlipPage', summary: 'Payroll slip page', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Payroll'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:payroll:slip:page')]
    public function page(): Result
    {
        return $this->success($this->repository->page($this->request->all(), $this->context()));
    }

    #[Post(path: '/admin/education/payroll/salary-slips/{id}/adjustments', operationId: 'educationPayrollSalarySlipAdjustment', summary: 'Payroll slip adjust', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Payroll'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:payroll:slip:adjust')]
    public function adjustment(int $id, SalaryAdjustmentRequest $request): Result
    {
        $context = $this->context();
        $result = $this->reviewService->adjustSlip($id, $request->validated(), $context);
        $this->audit($this->events, 'education.payroll.slip.adjusted', 'salary_slip', $id, $context, $result);

        return $this->success($result);
    }
}
