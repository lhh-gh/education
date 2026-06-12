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

namespace App\Http\Admin\Controller\Education\Foundation;

use App\Exception\BusinessException;
use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\Education\Foundation\ResolveEducationContextMiddleware;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Education\Foundation\AuditLogDetailRequest;
use App\Http\Admin\Request\Education\Foundation\AuditLogPageRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use App\Schema\Education\Foundation\AuditLogSchema;
use App\Service\Education\Foundation\AuditLogService;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\Context\Context;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Mine\Access\Attribute\Permission;
use Mine\Swagger\Attributes\PageResponse;
use Mine\Swagger\Attributes\ResultResponse;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: ResolveEducationContextMiddleware::class, priority: 98)]
final class AuditLogController extends AbstractController
{
    public function __construct(
        private readonly AuditLogService $service
    ) {}

    #[Get(
        path: '/admin/education/foundation/audit-logs/page',
        operationId: 'educationAuditLogPage',
        summary: 'Audit log page',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['Education Foundation'],
    )]
    #[PageResponse(instance: AuditLogSchema::class)]
    #[Permission(code: 'education:foundation:audit-log:page')]
    public function page(AuditLogPageRequest $request): Result
    {
        return $this->success(
            $this->service->page($request->validated(), $this->context())
        );
    }

    #[Get(
        path: '/admin/education/foundation/audit-logs/{id}',
        operationId: 'educationAuditLogDetail',
        summary: 'Audit log detail',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['Education Foundation'],
    )]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:foundation:audit-log:detail')]
    public function detail(int $id, AuditLogDetailRequest $request): Result
    {
        return $this->success(
            $this->service->detail($id, $this->context())
        );
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
