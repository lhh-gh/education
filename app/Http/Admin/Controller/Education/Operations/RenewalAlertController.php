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
use App\Http\Admin\Request\Education\Operations\RenewalAlertPageRequest;
use App\Http\Admin\Request\Education\Operations\RenewalTaskAssignRequest;
use App\Http\Admin\Request\Education\Operations\StudentFollowRecordSaveRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use App\Repository\Education\Operations\RenewalAlertRepository;
use App\Service\Education\Foundation\EducationUserContext;
use App\Service\Education\Operations\RenewalAlertService;
use Hyperf\Context\Context;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\Post;
use Mine\Access\Attribute\Permission;
use Mine\Swagger\Attributes\PageResponse;
use Mine\Swagger\Attributes\ResultResponse;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: ResolveEducationContextMiddleware::class, priority: 98)]
final class RenewalAlertController extends AbstractController
{
    public function __construct(
        private readonly RenewalAlertService $service,
        private readonly RenewalAlertRepository $repository
    ) {}

    #[Get(path: '/admin/education/operations/renewal-alerts/page', operationId: 'educationOperationRenewalAlertPage', summary: 'Renewal alert page', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Operations'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:operations:renewal-alert:page')]
    public function page(RenewalAlertPageRequest $request): Result
    {
        return $this->success($this->repository->pageOpen($request->validated(), $this->context()->tenantId));
    }

    #[Post(path: '/admin/education/operations/renewal-alerts/{id}/assign', operationId: 'educationOperationRenewalAlertAssign', summary: 'Assign renewal task', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Operations'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:operations:renewal-task:follow')]
    public function assign(int $id, RenewalTaskAssignRequest $request): Result
    {
        $data = $request->validated();

        return $this->success($this->service->assignTask($id, (int) $data['assignee_id'], $this->context(), $data['next_follow_at'] ?? null));
    }

    #[Post(path: '/admin/education/operations/renewal-tasks/{id}/follow', operationId: 'educationOperationRenewalTaskFollow', summary: 'Follow renewal task', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Operations'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:operations:renewal-task:follow')]
    public function follow(int $id, StudentFollowRecordSaveRequest $request): Result
    {
        return $this->success($this->service->saveFollowUp($id, $request->validated(), $this->context()));
    }

    #[Post(path: '/admin/education/operations/renewal-alerts/{id}/close', operationId: 'educationOperationRenewalAlertClose', summary: 'Close renewal alert', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Operations'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:operations:renewal-task:follow')]
    public function close(int $id): Result
    {
        return $this->success($this->service->closeAlert($id, $this->context()));
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
