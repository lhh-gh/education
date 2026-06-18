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
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Result;
use App\Model\Education\Payroll\EducationTeacherSalaryReview;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Mine\Access\Attribute\Permission;
use Mine\Swagger\Attributes\PageResponse;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: ResolveEducationContextMiddleware::class, priority: 98)]
final class SalaryReviewController extends AbstractController
{
    use PayrollControllerTrait;

    #[Get(path: '/admin/education/payroll/salary-reviews/page', operationId: 'educationPayrollSalaryReviewPage', summary: 'Payroll review page', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Payroll'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:payroll:review:page')]
    public function page(): Result
    {
        $context = $this->context();
        $query = EducationTeacherSalaryReview::query();
        if (! $context->platformAccess) {
            $context->tenantId === null ? $query->whereRaw('1 = 0') : $query->where('tenant_id', $context->tenantId);
        }
        $page = max(1, (int) $this->request->input('page', 1));
        $pageSize = max(1, min(100, (int) $this->request->input('pageSize', 20)));
        $total = (clone $query)->count();
        $list = $query->orderByDesc('id')->forPage($page, $pageSize)->get()->map(static fn (EducationTeacherSalaryReview $row): array => $row->toArray())->all();

        return $this->success(['list' => $list, 'total' => $total]);
    }
}
