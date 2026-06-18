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
use App\Http\Admin\Request\Education\Ai\PromptTemplateSaveRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Ai\PromptTemplateService;
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
final class PromptTemplateController extends AbstractController
{
    use AiControllerTrait;

    public function __construct(private readonly PromptTemplateService $service, private readonly EventDispatcherInterface $events) {}

    #[Get(path: '/admin/education/ai/prompt-templates/page', operationId: 'educationAiPromptTemplatePage', summary: 'AI prompt template page', tags: ['Education AI'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:ai:prompt:page')]
    public function page(): Result
    {
        $context = $this->context();

        return $this->success($this->service->page($this->tenantId($context), $this->getCurrentPage(), $this->getPageSize()));
    }

    #[Post(path: '/admin/education/ai/prompt-templates', operationId: 'educationAiPromptTemplateSave', summary: 'AI prompt template save', tags: ['Education AI'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:ai:prompt:save')]
    public function save(PromptTemplateSaveRequest $request): Result
    {
        $context = $this->context();
        $data = $request->validated() + [
            'tenant_id' => $this->tenantId($context),
            'campus_id' => $context->currentCampusId,
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ];
        $result = $this->service->save($data);

        return $this->success($result);
    }

    #[Post(path: '/admin/education/ai/prompt-templates/{templateCode}/publish', operationId: 'educationAiPromptTemplatePublish', summary: 'AI prompt template publish', tags: ['Education AI'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:ai:prompt:save')]
    public function publish(string $templateCode): Result
    {
        $context = $this->context();
        $version = (int) $this->getRequest()->input('version', 1);
        $result = $this->service->publish($this->tenantId($context), $templateCode, $version);
        $this->audit($this->events, 'education.ai.prompt.published', 'prompt_template', $result['id'], $context, $result);

        return $this->success($result);
    }
}
