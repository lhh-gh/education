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
use App\Http\Admin\Request\Education\Operations\MakeupArrangeRequest;
use App\Http\Admin\Request\Education\Operations\MakeupEntitlementPageRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use App\Repository\Education\Operations\MakeupEntitlementRepository;
use App\Repository\Education\Operations\MakeupRecordRepository;
use App\Service\Education\Foundation\EducationUserContext;
use App\Service\Education\Operations\MakeupService;
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
final class MakeupController extends AbstractController
{
    public function __construct(
        private readonly MakeupService $service,
        private readonly MakeupEntitlementRepository $entitlementRepository,
        private readonly MakeupRecordRepository $recordRepository
    ) {}

    #[Get(path: '/admin/education/operations/makeup-entitlements/page', operationId: 'educationOperationMakeupEntitlementPage', summary: 'Makeup entitlement page', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Operations'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:operations:makeup:page')]
    public function entitlements(MakeupEntitlementPageRequest $request): Result
    {
        return $this->success($this->entitlementRepository->pageAvailable($request->validated(), $this->context()));
    }

    #[Post(path: '/admin/education/operations/makeup-entitlements/{id}/arrange', operationId: 'educationOperationMakeupArrange', summary: 'Arrange makeup', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Operations'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:operations:makeup:arrange')]
    public function arrange(int $id, MakeupArrangeRequest $request): Result
    {
        $data = $request->validated();
        $data['makeup_entitlement_id'] = $id;

        return $this->success($this->service->arrangeMakeup($data, $this->context()));
    }

    #[Post(path: '/admin/education/operations/makeup-records/{id}/cancel', operationId: 'educationOperationMakeupCancel', summary: 'Cancel makeup record', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Operations'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:operations:makeup:cancel')]
    public function cancel(int $id): Result
    {
        return $this->success($this->service->cancelArrangement($id, $this->context()));
    }

    #[Get(path: '/admin/education/operations/makeup-records/page', operationId: 'educationOperationMakeupRecordPage', summary: 'Makeup record page', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Operations'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:operations:makeup:page')]
    public function records(MakeupEntitlementPageRequest $request): Result
    {
        return $this->success($this->recordRepository->pageByStudent($request->validated(), $this->context()));
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
