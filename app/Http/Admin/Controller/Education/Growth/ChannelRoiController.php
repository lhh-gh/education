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
use App\Http\Admin\Request\Education\Growth\ChannelCostSaveRequest;
use App\Http\Admin\Request\Education\Growth\GrowthDashboardRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Model\Education\Growth\EducationGrowthChannelCost;
use App\Service\Education\Growth\ChannelRoiService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Get;
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
final class ChannelRoiController extends AbstractController
{
    use GrowthControllerTrait;

    public function __construct(private readonly ChannelRoiService $service, private readonly EventDispatcherInterface $events) {}

    #[Get(path: '/admin/education/growth/channel-roi', operationId: 'educationGrowthChannelRoi', summary: 'Growth channel ROI', tags: ['Education Growth'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:growth:channel-roi:page')]
    public function page(GrowthDashboardRequest $request): Result
    {
        $context = $this->context();
        $data = $request->validated();
        $sourceId = (int) ($data['source_id'] ?? 0);
        $metricDate = (string) ($data['metric_date'] ?? $data['start_date'] ?? date('Y-m-d'));
        if ($sourceId <= 0) {
            return $this->success(['list' => []]);
        }

        return $this->success(['list' => [$this->service->aggregateDaily($this->tenantId($context), $this->campusId($context), $metricDate, $sourceId)]]);
    }

    #[Post(path: '/admin/education/growth/channel-costs', operationId: 'educationGrowthChannelCostSave', summary: 'Growth channel cost save', tags: ['Education Growth'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:growth:channel-cost:save')]
    public function saveCost(ChannelCostSaveRequest $request): Result
    {
        $context = $this->context();
        $data = $request->validated() + [
            'tenant_id' => $this->tenantId($context),
            'campus_id' => $context->currentCampusId,
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ];
        $cost = EducationGrowthChannelCost::query()->create($data);
        $result = ['channel_cost_id' => (int) $cost->id, 'status' => 'saved'];
        $this->audit($this->events, 'education.growth.channel_cost.saved', 'channel_cost', $cost->id, $context, $result);

        return $this->success($result);
    }
}
