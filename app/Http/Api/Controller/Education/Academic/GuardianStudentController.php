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
use App\Http\Api\Request\Education\Academic\GuardianStudentListRequest;
use App\Http\Common\Controller\AbstractController;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Academic\GuardianMobileService;
use App\Service\Education\Foundation\MobileContextService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Mine\Swagger\Attributes\ResultResponse;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: MobileEducationContextMiddleware::class, priority: 99)]
final class GuardianStudentController extends AbstractController
{
    public function __construct(
        private readonly GuardianMobileService $service,
        private readonly MobileContextService $mobileContext
    ) {}

    #[Get(path: '/mobile/education/academic/guardian/students', operationId: 'educationMobileGuardianStudents', summary: 'Guardian bound students', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Mobile Academic'])]
    #[ResultResponse(instance: new Result())]
    public function students(GuardianStudentListRequest $request): Result
    {
        return $this->success($this->service->students($request->validated(), $this->mobileContext->mobile()));
    }
}
