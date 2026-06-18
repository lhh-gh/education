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

namespace App\Http\Admin\Controller\Education\Ai;

use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\Education\Foundation\ResolveEducationContextMiddleware;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Education\Ai\AiFeatureSettingSaveRequest;
use App\Http\Admin\Request\Education\Ai\AiModelConfigSaveRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Ai\AiConfigService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\Post;
use Mine\Access\Attribute\Permission;
use Mine\Swagger\Attributes\PageResponse;
use Mine\Swagger\Attributes\ResultResponse;
use Psr\EventDispatcher\EventDispatcherInterface;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: ResolveEducationContextMiddleware::class, priority: 98)]
#[Middleware(middleware: OperationMiddleware::class, priority: 97)]
final class AiModelConfigController extends AbstractController
{
    use AiControllerTrait;

    public function __construct(private readonly AiConfigService $service, private readonly EventDispatcherInterface $events) {}

    #[Get(path: '/admin/education/ai/model-configs/page', operationId: 'educationAiModelConfigPage', summary: 'AI model config page', tags: ['Education AI'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:ai:model-config:page')]
    public function page(): Result
    {
        $context = $this->context();

        return $this->success($this->service->pageModelConfigs($this->tenantId($context), $this->getCurrentPage(), $this->getPageSize()));
    }

    #[Post(path: '/admin/education/ai/model-configs', operationId: 'educationAiModelConfigSave', summary: 'AI model config save', tags: ['Education AI'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:ai:model-config:save')]
    public function save(AiModelConfigSaveRequest $request): Result
    {
        $context = $this->context();
        $data = $request->validated() + [
            'tenant_id' => $this->tenantId($context),
            'campus_id' => $context->currentCampusId,
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ];
        $result = $this->service->saveModelConfig($data);
        $this->audit($this->events, 'education.ai.config.saved', 'model_config', $result['id'], $context, $result);

        return $this->success($result);
    }

    #[Post(path: '/admin/education/ai/feature-settings', operationId: 'educationAiFeatureSettingSave', summary: 'AI feature setting save', tags: ['Education AI'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:ai:model-config:save')]
    public function saveFeatureSetting(AiFeatureSettingSaveRequest $request): Result
    {
        $context = $this->context();
        $data = $request->validated() + [
            'tenant_id' => $this->tenantId($context),
            'campus_id' => $context->currentCampusId,
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ];
        $result = $this->service->saveFeatureSetting($data);
        $this->audit($this->events, 'education.ai.config.saved', 'feature_setting', $result['id'], $context, $result);

        return $this->success($result);
    }
}
