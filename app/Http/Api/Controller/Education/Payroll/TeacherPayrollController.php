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

namespace App\Http\Api\Controller\Education\Payroll;

use App\Http\Api\Middleware\Education\Foundation\MobileEducationContextMiddleware;
use App\Http\Api\Request\Education\Payroll\TeacherSalarySlipPageRequest;
use App\Http\Api\Request\Education\Payroll\TeacherWorkloadDisputeRequest;
use App\Http\Common\Controller\AbstractController;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Foundation\MobileContextService;
use App\Service\Education\Payroll\TeacherPayrollService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\Post;
use Mine\Swagger\Attributes\PageResponse;
use Mine\Swagger\Attributes\ResultResponse;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: MobileEducationContextMiddleware::class, priority: 99)]
final class TeacherPayrollController extends AbstractController
{
    public function __construct(private readonly TeacherPayrollService $service, private readonly MobileContextService $mobileContext) {}

    #[Get(path: '/mobile/education/payroll/teacher/slips', operationId: 'educationMobileTeacherPayrollSlips', summary: 'Teacher payroll slips', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Mobile Payroll'])]
    #[PageResponse(instance: new Result())]
    public function slips(TeacherSalarySlipPageRequest $request): Result
    {
        return $this->success($this->service->slips($request->validated(), $this->mobileContext->mobile()));
    }

    #[Get(path: '/mobile/education/payroll/teacher/disputes', operationId: 'educationMobileTeacherPayrollDisputes', summary: 'Teacher payroll disputes', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Mobile Payroll'])]
    #[PageResponse(instance: new Result())]
    public function disputes(TeacherSalarySlipPageRequest $request): Result
    {
        return $this->success($this->service->disputes($request->validated(), $this->mobileContext->mobile()));
    }

    #[Post(path: '/mobile/education/payroll/teacher/disputes', operationId: 'educationMobileTeacherPayrollDisputeSubmit', summary: 'Teacher payroll dispute submit', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Mobile Payroll'])]
    #[ResultResponse(instance: new Result())]
    public function submitDispute(TeacherWorkloadDisputeRequest $request): Result
    {
        return $this->success($this->service->submitDispute($request->validated(), $this->mobileContext->mobile()));
    }
}
