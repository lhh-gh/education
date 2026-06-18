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

namespace App\Http\Admin\Controller\Education\Admissions;

use App\Exception\BusinessException;
use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\Education\Foundation\ResolveEducationContextMiddleware;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Education\Admissions\LeadPageRequest;
use App\Http\Admin\Request\Education\Admissions\LeadSourceSaveRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use App\Service\Education\Admissions\LeadSourceService;
use App\Service\Education\Foundation\EducationUserContext;
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
final class LeadSourceController extends AbstractController
{
    public function __construct(private readonly LeadSourceService $service) {}

    #[Get(path: '/admin/education/admissions/lead-sources/page', operationId: 'educationAdmissionLeadSourcePage', summary: 'Lead source page', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Admissions'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:admissions:lead-source:page')]
    public function page(LeadPageRequest $request): Result
    {
        return $this->success($this->service->page($request->validated(), $this->context()));
    }

    #[Post(path: '/admin/education/admissions/lead-sources', operationId: 'educationAdmissionLeadSourceCreate', summary: 'Create lead source', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Admissions'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:admissions:lead-source:create')]
    public function create(LeadSourceSaveRequest $request): Result
    {
        return $this->success($this->service->create($request->validated(), $this->context()));
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
