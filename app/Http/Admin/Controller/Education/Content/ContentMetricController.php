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

namespace App\Http\Admin\Controller\Education\Content;

use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\Education\Foundation\ResolveEducationContextMiddleware;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Model\Education\Content\EducationMaterialUsageMetricDaily;
use App\Model\Education\Content\EducationStudentWorkMetricDaily;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Mine\Access\Attribute\Permission;
use Mine\Swagger\Attributes\ResultResponse;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: ResolveEducationContextMiddleware::class, priority: 98)]
#[Middleware(middleware: OperationMiddleware::class, priority: 97)]
final class ContentMetricController extends AbstractController
{
    use ContentControllerTrait;

    #[Get(path: '/admin/education/content/material-usage-metrics', operationId: 'educationContentMaterialUsageMetricPage', summary: 'Content material metrics', tags: ['Education Content'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:content:metric:page')]
    public function materialUsage(RequestInterface $request): Result
    {
        $context = $this->context();
        $query = EducationMaterialUsageMetricDaily::query()->where('tenant_id', $this->tenantId($context));
        foreach (['campus_id', 'course_id', 'material_id'] as $field) {
            if ($request->input($field) !== null && $request->input($field) !== '') {
                $query->where($field, (int) $request->input($field));
            }
        }
        $this->applyDateRange($query, $request);
        $total = (int) (clone $query)->count();

        return $this->success([
            'list' => $query->orderByDesc('metric_date')->forPage($this->pageNumber($request), $this->pageSize($request))->get()->toArray(),
            'total' => $total,
        ]);
    }

    #[Get(path: '/admin/education/content/student-work-metrics', operationId: 'educationContentStudentWorkMetricPage', summary: 'Content work metrics', tags: ['Education Content'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:content:metric:page')]
    public function studentWork(RequestInterface $request): Result
    {
        $context = $this->context();
        $query = EducationStudentWorkMetricDaily::query()->where('tenant_id', $this->tenantId($context));
        foreach (['campus_id', 'student_id', 'teacher_id'] as $field) {
            if ($request->input($field) !== null && $request->input($field) !== '') {
                $query->where($field, (int) $request->input($field));
            }
        }
        $this->applyDateRange($query, $request);
        $total = (int) (clone $query)->count();

        return $this->success([
            'list' => $query->orderByDesc('metric_date')->forPage($this->pageNumber($request), $this->pageSize($request))->get()->toArray(),
            'total' => $total,
        ]);
    }

    private function applyDateRange(mixed $query, RequestInterface $request): void
    {
        if ($request->input('start_date')) {
            $query->where('metric_date', '>=', (string) $request->input('start_date'));
        }
        if ($request->input('end_date')) {
            $query->where('metric_date', '<=', (string) $request->input('end_date'));
        }
    }
}
