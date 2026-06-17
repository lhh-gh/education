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

namespace App\Http\Admin\Controller\Education\Group;

use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\Education\Foundation\ResolveEducationContextMiddleware;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Education\Group\ContractSaveRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Group\ContractService;
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
final class ContractController extends AbstractController
{
    use GroupControllerTrait;

    public function __construct(private readonly ContractService $service, private readonly EventDispatcherInterface $events) {}

    #[Get(path: '/admin/education/group/contracts/page', operationId: 'educationGroupContractPage', summary: 'Group contract page', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Group'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:group:contract:page')]
    public function page(): Result
    {
        return $this->success($this->service->page($this->getRequestData(), $this->context()));
    }

    #[Post(path: '/admin/education/group/contracts', operationId: 'educationGroupContractSave', summary: 'Group contract save', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Group'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:group:contract:create')]
    public function save(ContractSaveRequest $request): Result
    {
        $context = $this->context();
        $result = $this->service->save($request->validated(), $context);
        $this->audit($this->events, 'education.group.contract.saved', 'contract', $result['contract_id'], $context, $result);

        return $this->success($result);
    }

    #[Post(path: '/admin/education/group/contracts/{id}/submit-review', operationId: 'educationGroupContractSubmitReview', summary: 'Group contract submit review', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Group'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:group:contract:submit-review')]
    public function submitReview(int $id): Result
    {
        $context = $this->context();
        $result = $this->service->submitReview($id, $context);
        $this->audit($this->events, 'education.group.contract.submitted', 'contract', $result['contract_id'], $context, $result);

        return $this->success($result);
    }
}
