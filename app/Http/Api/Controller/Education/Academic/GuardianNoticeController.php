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
use App\Http\Api\Request\Education\Academic\GuardianNoticeDetailRequest;
use App\Http\Api\Request\Education\Academic\GuardianNoticePageRequest;
use App\Http\Api\Request\Education\Academic\GuardianNoticeReadRequest;
use App\Http\Common\Controller\AbstractController;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Academic\GuardianMobileService;
use App\Service\Education\Foundation\MobileContextService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\Put;
use Mine\Swagger\Attributes\PageResponse;
use Mine\Swagger\Attributes\ResultResponse;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: MobileEducationContextMiddleware::class, priority: 99)]
final class GuardianNoticeController extends AbstractController
{
    public function __construct(
        private readonly GuardianMobileService $service,
        private readonly MobileContextService $mobileContext
    ) {}

    #[Get(path: '/mobile/education/academic/guardian/notices/page', operationId: 'educationMobileGuardianNoticePage', summary: 'Guardian notice page', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Mobile Academic'])]
    #[PageResponse(instance: new Result())]
    public function page(GuardianNoticePageRequest $request): Result
    {
        return $this->success($this->service->noticePage($request->validated(), $this->mobileContext->mobile()));
    }

    #[Get(path: '/mobile/education/academic/guardian/notices/{receiptId}', operationId: 'educationMobileGuardianNoticeDetail', summary: 'Guardian notice detail', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Mobile Academic'])]
    #[ResultResponse(instance: new Result())]
    public function detail(int $receiptId, GuardianNoticeDetailRequest $request): Result
    {
        return $this->success($this->service->noticeDetail($receiptId, $this->mobileContext->mobile()));
    }

    #[Put(path: '/mobile/education/academic/guardian/notices/{receiptId}/read', operationId: 'educationMobileGuardianNoticeRead', summary: 'Guardian notice read', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Mobile Academic'])]
    #[Middleware(middleware: OperationMiddleware::class, priority: 98)]
    #[ResultResponse(instance: new Result())]
    public function read(int $receiptId, GuardianNoticeReadRequest $request): Result
    {
        return $this->success($this->service->readNotice($receiptId, $this->mobileContext->mobile()));
    }
}
