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

namespace App\Http\Admin\Controller\Education\Academic;

use App\Exception\BusinessException;
use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\Education\Foundation\ResolveEducationContextMiddleware;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Education\Academic\ConsumptionPageRequest;
use App\Http\Admin\Request\Education\Academic\ConsumptionRollbackRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use App\Http\CurrentUser;
use App\Schema\Education\Academic\ConsumptionSchema;
use App\Service\Education\Academic\LessonConsumptionService;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\Context\Context;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\JsonContent;
use Hyperf\Swagger\Annotation\Post;
use Hyperf\Swagger\Annotation\RequestBody;
use Mine\Access\Attribute\Permission;
use Mine\Swagger\Attributes\PageResponse;
use Mine\Swagger\Attributes\ResultResponse;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: ResolveEducationContextMiddleware::class, priority: 98)]
#[Middleware(middleware: OperationMiddleware::class, priority: 97)]
final class ConsumptionController extends AbstractController
{
    public function __construct(
        private readonly LessonConsumptionService $service,
        private readonly CurrentUser $currentUser
    ) {}

    #[Get(path: '/admin/education/academic/consumptions/page', operationId: 'educationAcademicConsumptionPage', summary: 'Consumption page', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[PageResponse(instance: ConsumptionSchema::class)]
    #[Permission(code: 'education:academic:consumption:page')]
    public function page(ConsumptionPageRequest $request): Result
    {
        return $this->success($this->service->page($request->validated(), $this->context()));
    }

    #[Get(path: '/admin/education/academic/consumptions/{id}', operationId: 'educationAcademicConsumptionDetail', summary: 'Consumption detail', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:consumption:detail')]
    public function detail(int $id): Result
    {
        return $this->success($this->service->detail($id, $this->context())->toArray());
    }

    #[Post(path: '/admin/education/academic/consumptions/{id}/rollback', operationId: 'educationAcademicConsumptionRollback', summary: 'Rollback consumption', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Academic'])]
    #[RequestBody(content: new JsonContent(ref: ConsumptionRollbackRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:academic:consumption:rollback')]
    public function rollback(int $id, ConsumptionRollbackRequest $request): Result
    {
        return $this->success($this->service->rollback($id, $request->validated()['reason'], $this->context(), $this->currentUser->id()));
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
