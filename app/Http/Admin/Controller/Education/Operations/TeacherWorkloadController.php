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

namespace App\Http\Admin\Controller\Education\Operations;

use App\Exception\BusinessException;
use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\Education\Foundation\ResolveEducationContextMiddleware;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Education\Operations\TeacherWorkloadPageRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use App\Model\Education\Operations\EducationTeacherWorkloadRecord;
use App\Service\Education\Foundation\EducationUserContext;
use App\Service\Education\Operations\TeacherWorkloadService;
use Hyperf\Context\Context;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Mine\Access\Attribute\Permission;
use Mine\Swagger\Attributes\PageResponse;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: ResolveEducationContextMiddleware::class, priority: 98)]
final class TeacherWorkloadController extends AbstractController
{
    public function __construct(private readonly TeacherWorkloadService $service) {}

    #[Get(path: '/admin/education/operations/reports/teacher-workloads', operationId: 'educationOperationTeacherWorkloads', summary: 'Teacher workload report', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Operations'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:operations:teacher-workload:report')]
    public function report(TeacherWorkloadPageRequest $request): Result
    {
        $params = $request->validated();
        $context = $this->context();
        $query = EducationTeacherWorkloadRecord::query()->where('tenant_id', $context->tenantId);
        foreach (['campus_id', 'teacher_id', 'workload_type'] as $field) {
            if (isset($params[$field]) && $params[$field] !== '') {
                $query->where($field, $params[$field]);
            }
        }
        $list = $query->orderByDesc('recorded_at')->get()->map(static fn ($row): array => $row->toArray())->all();

        return $this->success(['list' => $list, 'total' => \count($list), 'summary' => isset($params['teacher_id']) ? $this->service->summaryByTeacher($context->tenantId, (int) $params['teacher_id']) : []]);
    }

    private function context(): EducationUserContext
    {
        $context = Context::get(ResolveEducationContextMiddleware::CONTEXT_KEY);
        if (! $context instanceof EducationUserContext) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'education user context is missing');
        }

        return $context;
    }
}
