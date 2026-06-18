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

namespace App\Http\Admin\Controller\Education\Growth;

use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\Education\Foundation\ResolveEducationContextMiddleware;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Education\Growth\AiTalkScriptConfirmRequest;
use App\Http\Admin\Request\Education\Growth\AiTalkScriptGenerateRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Growth\ConsultantAiService;
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
final class AiTalkScriptController extends AbstractController
{
    use GrowthControllerTrait;

    public function __construct(private readonly ConsultantAiService $service, private readonly EventDispatcherInterface $events) {}

    #[Post(path: '/admin/education/growth/ai-talk-scripts/generate', operationId: 'educationGrowthAiTalkScriptGenerate', summary: 'Growth AI talk script generate', tags: ['Education Growth'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:growth:ai-script:generate')]
    public function generate(AiTalkScriptGenerateRequest $request): Result
    {
        $context = $this->context();
        $result = $this->service->generateScript($request->validated() + [
            'tenant_id' => $this->tenantId($context),
            'campus_id' => $context->currentCampusId,
            'created_by' => $context->userId,
        ]);
        $this->audit($this->events, 'education.growth.ai_script.generated', 'ai_talk_script', $result['ai_talk_script_id'], $context, $result);

        return $this->success($result);
    }

    #[Post(path: '/admin/education/growth/ai-talk-scripts/{id}/confirm', operationId: 'educationGrowthAiTalkScriptConfirm', summary: 'Growth AI talk script confirm', tags: ['Education Growth'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:growth:ai-script:confirm')]
    public function confirm(int $id, AiTalkScriptConfirmRequest $request): Result
    {
        $context = $this->context();
        $result = $this->service->confirmScript($id, $context->userId, (string) $request->validated()['edited_script']);
        $this->audit($this->events, 'education.growth.ai_script.confirmed', 'ai_talk_script', $id, $context, $result);

        return $this->success($result);
    }
}
