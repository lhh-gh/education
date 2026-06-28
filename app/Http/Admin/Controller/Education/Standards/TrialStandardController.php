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

namespace App\Http\Admin\Controller\Education\Standards;

use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\Education\Foundation\ResolveEducationContextMiddleware;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Education\Standards\TrialStandardSaveRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Standards\TrialStandardService;
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
final class TrialStandardController extends AbstractController
{
    use StandardsControllerTrait;

    public function __construct(private readonly TrialStandardService $service, private readonly EventDispatcherInterface $events) {}

    #[Post(path: '/admin/education/standards/trial-standards', operationId: 'educationStandardsTrialSave', summary: 'Standards trial save', tags: ['Education Standards'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:standards:trial:save')]
    public function save(TrialStandardSaveRequest $request): Result
    {
        $context = $this->context();
        $data = $request->validated();
        $items = $data['items'] ?? [];
        unset($data['items']);
        $result = $this->service->save($data + ['tenant_id' => $this->tenantId($context), 'campus_id' => $this->campusId($context)], $context);
        if ($items !== []) {
            $this->service->saveItems($this->tenantId($context), $this->campusId($context), $result['trial_standard_id'], $items);
        }
        $this->audit($this->events, 'education.standards.trial.saved', 'trial_standard', $result['trial_standard_id'], $context, $result);

        return $this->success($result);
    }
}
