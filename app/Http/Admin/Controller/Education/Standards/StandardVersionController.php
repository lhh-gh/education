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

namespace App\Http\Admin\Controller\Education\Standards;

use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\Education\Foundation\ResolveEducationContextMiddleware;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Education\Standards\LocalizationOverrideSaveRequest;
use App\Http\Admin\Request\Education\Standards\StandardPublishRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Standards\StandardVersionService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\Post;
use Mine\Access\Attribute\Permission;
use Mine\Swagger\Attributes\ResultResponse;
use Psr\EventDispatcher\EventDispatcherInterface;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: ResolveEducationContextMiddleware::class, priority: 98)]
#[Middleware(middleware: OperationMiddleware::class, priority: 97)]
final class StandardVersionController extends AbstractController
{
    use StandardsControllerTrait;

    public function __construct(private readonly StandardVersionService $service, private readonly EventDispatcherInterface $events) {}

    #[Post(path: '/admin/education/standards/versions/{id}/publish', operationId: 'educationStandardsVersionPublish', summary: 'Standards version publish', tags: ['Education Standards'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:standards:version:publish')]
    public function publish(int $id, StandardPublishRequest $request): Result
    {
        $context = $this->context();
        try {
            $result = $this->service->publish($this->tenantId($context), $this->campusId($context), $id, $context->userId, true);
        } catch (\RuntimeException $exception) {
            throw $this->businessFailure($exception);
        }
        $this->audit($this->events, 'education.standards.standard.published', 'standard_version', $id, $context, $result + $request->validated());

        return $this->success($result);
    }

    #[Post(path: '/admin/education/standards/versions/{id}/localization-overrides', operationId: 'educationStandardsLocalizationSave', summary: 'Standards localization save', tags: ['Education Standards'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:standards:version:localization')]
    public function localization(int $id, LocalizationOverrideSaveRequest $request): Result
    {
        $context = $this->context();
        $result = $this->service->saveLocalizationOverride($request->validated() + [
            'tenant_id' => $this->tenantId($context),
            'campus_id' => $this->campusId($context),
            'standard_version_id' => $id,
        ]);
        $this->audit($this->events, 'education.standards.localization.published', 'localization_override', $result['localization_override_id'], $context, $result);

        return $this->success($result);
    }
}
