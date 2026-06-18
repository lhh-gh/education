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
use App\Http\Admin\Request\Education\Payroll\TeacherPerformancePageRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Payroll\TeacherPerformanceService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Mine\Access\Attribute\Permission;
use Mine\Swagger\Attributes\PageResponse;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: ResolveEducationContextMiddleware::class, priority: 98)]
final class TeacherPerformanceController extends AbstractController
{
    use PayrollControllerTrait;

    public function __construct(private readonly TeacherPerformanceService $service) {}

    #[Get(path: '/admin/education/payroll/teacher-performance/page', operationId: 'educationPayrollTeacherPerformancePage', summary: 'Payroll performance page', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Payroll'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:payroll:performance:page')]
    public function page(TeacherPerformancePageRequest $request): Result
    {
        return $this->success($this->service->page($request->validated(), $this->context()));
    }

    #[Get(path: '/admin/education/payroll/teacher-performance/summary', operationId: 'educationPayrollTeacherPerformanceSummary', summary: 'Payroll performance summary', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Payroll'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:payroll:performance:page')]
    public function summary(TeacherPerformancePageRequest $request): Result
    {
        return $this->success($this->service->summary($request->validated(), $this->context()));
    }
}
