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
use App\Http\Admin\Request\Education\Family\HomeworkAssignmentSaveRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Family\HomeworkService;
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
final class HomeworkAssignmentController extends AbstractController
{
    use FamilyControllerTrait;

    public function __construct(private readonly HomeworkService $service, private readonly EventDispatcherInterface $events) {}

    #[Get(path: '/admin/education/family/homework-assignments/page', operationId: 'educationFamilyHomeworkPage', summary: 'Family homework page', tags: ['Education Family'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:family:homework:page')]
    public function page(): Result
    {
        return $this->success($this->service->pageAssignments($this->getRequestData(), $this->context()));
    }

    #[Post(path: '/admin/education/family/homework-assignments', operationId: 'educationFamilyHomeworkSave', summary: 'Family homework save', tags: ['Education Family'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:family:homework:create')]
    public function save(HomeworkAssignmentSaveRequest $request): Result
    {
        $context = $this->context();
        $result = $this->service->publishAssignment($request->validated(), $context);
        $this->audit($this->events, 'education.family.homework.published', 'homework_assignment', $result['homework_assignment_id'], $context, $result);

        return $this->success($result);
    }
}
