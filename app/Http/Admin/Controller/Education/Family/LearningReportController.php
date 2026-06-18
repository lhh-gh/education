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
use App\Http\Admin\Request\Education\Family\LearningReportSaveRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Family\LearningReportService;
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
final class LearningReportController extends AbstractController
{
    use FamilyControllerTrait;

    public function __construct(private readonly LearningReportService $service, private readonly EventDispatcherInterface $events) {}

    #[Get(path: '/admin/education/family/learning-reports/page', operationId: 'educationFamilyReportPage', summary: 'Family report page', tags: ['Education Family'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:family:report:page')]
    public function page(): Result
    {
        return $this->success(['list' => [], 'total' => 0]);
    }

    #[Post(path: '/admin/education/family/learning-reports', operationId: 'educationFamilyReportSave', summary: 'Family report save', tags: ['Education Family'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:family:report:create')]
    public function save(LearningReportSaveRequest $request): Result
    {
        $context = $this->context();
        $result = $this->service->save($request->validated(), $context);

        return $this->success($result);
    }

    #[Post(path: '/admin/education/family/learning-reports/{id}/publish', operationId: 'educationFamilyReportPublish', summary: 'Family report publish', tags: ['Education Family'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:family:report:publish')]
    public function publish(int $id): Result
    {
        $context = $this->context();
        $result = $this->service->publish($id, $context);
        $this->audit($this->events, 'education.family.report.published', 'learning_report', $result['learning_report_id'], $context, $result);

        return $this->success($result);
    }

    #[Post(path: '/admin/education/family/learning-reports/{id}/withdraw', operationId: 'educationFamilyReportWithdraw', summary: 'Family report withdraw', tags: ['Education Family'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:family:report:publish')]
    public function withdraw(int $id): Result
    {
        $context = $this->context();
        $result = $this->service->withdraw($id, $context);
        $this->audit($this->events, 'education.family.report.withdrawn', 'learning_report', $result['learning_report_id'], $context, $result);

        return $this->success($result);
    }
}
