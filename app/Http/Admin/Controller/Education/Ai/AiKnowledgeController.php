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
use App\Http\Admin\Request\Education\Ai\AiKnowledgeDocumentSaveRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Ai\AiKnowledgeService;
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
final class AiKnowledgeController extends AbstractController
{
    use AiControllerTrait;

    public function __construct(private readonly AiKnowledgeService $service, private readonly EventDispatcherInterface $events) {}

    #[Post(path: '/admin/education/ai/knowledge-documents', operationId: 'educationAiKnowledgeDocumentSave', summary: 'AI knowledge document save', tags: ['Education AI'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:ai:knowledge:save')]
    public function save(AiKnowledgeDocumentSaveRequest $request): Result
    {
        $context = $this->context();
        $data = $request->validated() + [
            'tenant_id' => $this->tenantId($context),
            'campus_id' => $context->currentCampusId,
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ];
        $result = $this->service->save($data);
        $this->audit($this->events, 'education.ai.knowledge.saved', 'knowledge_document', $result['id'], $context, $result);

        return $this->success($result);
    }
}
