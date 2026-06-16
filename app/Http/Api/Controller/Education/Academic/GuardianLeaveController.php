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

namespace App\Http\Api\Controller\Education\Academic;

use App\Http\Api\Middleware\Education\Foundation\MobileEducationContextMiddleware;
use App\Http\Api\Request\Education\Academic\GuardianLeaveCreateRequest;
use App\Http\Common\Controller\AbstractController;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Academic\GuardianMobileLeaveService;
use App\Service\Education\Foundation\MobileContextService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\JsonContent;
use Hyperf\Swagger\Annotation\Post;
use Hyperf\Swagger\Annotation\RequestBody;
use Mine\Swagger\Attributes\ResultResponse;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: MobileEducationContextMiddleware::class, priority: 99)]
final class GuardianLeaveController extends AbstractController
{
    public function __construct(
        private readonly GuardianMobileLeaveService $service,
        private readonly MobileContextService $mobileContext
    ) {}

    #[Post(path: '/mobile/education/academic/guardian/leave-requests', operationId: 'educationMobileGuardianLeaveCreate', summary: 'Guardian leave create', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Mobile Academic'])]
    #[Middleware(middleware: OperationMiddleware::class, priority: 98)]
    #[RequestBody(content: new JsonContent(ref: GuardianLeaveCreateRequest::class))]
    #[ResultResponse(instance: new Result())]
    public function create(GuardianLeaveCreateRequest $request): Result
    {
        return $this->success($this->service->create($request->validated(), $this->mobileContext->mobile()));
    }
}
