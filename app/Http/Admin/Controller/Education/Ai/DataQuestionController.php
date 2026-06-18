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
use App\Http\Admin\Request\Education\Ai\DataQuestionRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Ai\DataQuestionService;
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
final class DataQuestionController extends AbstractController
{
    use AiControllerTrait;

    public function __construct(private readonly DataQuestionService $service, private readonly EventDispatcherInterface $events) {}

    #[Get(path: '/admin/education/ai/data-questions/page', operationId: 'educationAiDataQuestionPage', summary: 'AI data question page', tags: ['Education AI'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:ai:data-question:page')]
    public function page(): Result
    {
        $context = $this->context();

        return $this->success($this->service->pageLogs($this->tenantId($context), $this->getCurrentPage(), $this->getPageSize()));
    }

    #[Get(path: '/admin/education/ai/metric-catalogs/page', operationId: 'educationAiMetricCatalogPage', summary: 'AI metric catalog page', tags: ['Education AI'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:ai:data-question:create')]
    public function metricCatalogs(): Result
    {
        $context = $this->context();

        return $this->success($this->service->pageMetricCatalogs($this->tenantId($context), $this->getCurrentPage(), $this->getPageSize()));
    }

    #[Post(path: '/admin/education/ai/data-questions', operationId: 'educationAiDataQuestionCreate', summary: 'AI data question create', tags: ['Education AI'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:ai:data-question:create')]
    public function ask(DataQuestionRequest $request): Result
    {
        $context = $this->context();
        $result = $this->service->ask($request->validated(), $context);
        $this->audit($this->events, 'education.ai.data_question.asked', 'data_question', $result['question_log_id'], $context, $result);

        return $this->success($result);
    }
}
