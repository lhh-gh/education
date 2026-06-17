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

namespace App\Http\Admin\Controller\Education\Family;

use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\Education\Foundation\ResolveEducationContextMiddleware;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Education\Family\FamilyMessagePageRequest;
use App\Http\Api\Request\Education\Family\FamilyMessageSendRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Family\FamilyMessageService;
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
final class FamilyMessageMonitorController extends AbstractController
{
    use FamilyControllerTrait;

    public function __construct(private readonly FamilyMessageService $service, private readonly EventDispatcherInterface $events) {}

    #[Get(path: '/admin/education/family/messages/page', operationId: 'educationFamilyMessagePage', summary: 'Family message page', tags: ['Education Family'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:family:message:page')]
    public function page(FamilyMessagePageRequest $request): Result
    {
        return $this->success(['list' => [], 'total' => 0, 'filters' => $request->validated()]);
    }

    #[Get(path: '/admin/education/family/messages/thread', operationId: 'educationFamilyMessageThread', summary: 'Family message thread', tags: ['Education Family'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:family:message:page')]
    public function thread(FamilyMessagePageRequest $request): Result
    {
        $data = $request->validated();

        return $this->success($this->service->thread((int) ($data['student_id'] ?? 0), (string) ($data['thread_id'] ?? ''), $this->context()));
    }

    #[Post(path: '/admin/education/family/messages', operationId: 'educationFamilyMessageSend', summary: 'Family message send', tags: ['Education Family'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:family:message:reply')]
    public function send(FamilyMessageSendRequest $request): Result
    {
        $context = $this->context();
        $result = $this->service->send($request->validated(), $context);
        $this->audit($this->events, 'education.family.message.sent', 'family_message', $result['message_id'], $context, $result);

        return $this->success($result);
    }
}
