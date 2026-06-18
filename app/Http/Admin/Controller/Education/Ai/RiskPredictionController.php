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
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Ai\RiskPredictionService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Mine\Access\Attribute\Permission;
use Mine\Swagger\Attributes\PageResponse;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: ResolveEducationContextMiddleware::class, priority: 98)]
#[Middleware(middleware: OperationMiddleware::class, priority: 97)]
final class RiskPredictionController extends AbstractController
{
    use AiControllerTrait;

    public function __construct(private readonly RiskPredictionService $service) {}

    #[Get(path: '/admin/education/ai/risk-scores/page', operationId: 'educationAiRiskScorePage', summary: 'AI risk score page', tags: ['Education AI'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:ai:risk-score:page')]
    public function page(): Result
    {
        $context = $this->context();

        return $this->success($this->service->page($this->tenantId($context), $this->getCurrentPage(), $this->getPageSize()));
    }
}
