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
use App\Http\Admin\Request\Education\Growth\LeadLossRecordSaveRequest;
use App\Http\Admin\Request\Education\Growth\LossReasonSaveRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Growth\LossReasonService;
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
final class LossReasonController extends AbstractController
{
    use GrowthControllerTrait;

    public function __construct(private readonly LossReasonService $service, private readonly EventDispatcherInterface $events) {}

    #[Post(path: '/admin/education/growth/loss-reasons', operationId: 'educationGrowthLossReasonSave', summary: 'Growth loss reason save', tags: ['Education Growth'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:growth:loss-reason:save')]
    public function saveReason(LossReasonSaveRequest $request): Result
    {
        $context = $this->context();
        $result = $this->service->saveReason($request->validated() + [
            'tenant_id' => $this->tenantId($context),
            'campus_id' => $context->currentCampusId,
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ]);
        $this->audit($this->events, 'education.growth.loss_reason.saved', 'loss_reason', $result['reason_id'], $context, $result);

        return $this->success($result);
    }

    #[Post(path: '/admin/education/growth/leads/{leadId}/loss-records', operationId: 'educationGrowthLeadLossRecordCreate', summary: 'Growth lead loss record create', tags: ['Education Growth'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:growth:loss:create')]
    public function createLossRecord(int $leadId, LeadLossRecordSaveRequest $request): Result
    {
        $context = $this->context();
        $result = $this->service->createLossRecord($request->validated() + [
            'tenant_id' => $this->tenantId($context),
            'campus_id' => $context->currentCampusId,
            'lead_id' => $leadId,
            'lost_by' => $context->userId,
            'lost_at' => date('Y-m-d H:i:s'),
            'created_by' => $context->userId,
            'updated_by' => $context->userId,
        ]);
        $this->audit($this->events, 'education.growth.loss_record.created', 'lead_loss_record', $leadId, $context, $result);

        return $this->success($result);
    }
}
