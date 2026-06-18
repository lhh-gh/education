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
use App\Http\Admin\Request\Education\Standards\ServiceTemplateSaveRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Standards\ServiceTemplateService;
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
final class ServiceTemplateController extends AbstractController
{
    use StandardsControllerTrait;

    public function __construct(private readonly ServiceTemplateService $service, private readonly EventDispatcherInterface $events) {}

    #[Post(path: '/admin/education/standards/service-templates', operationId: 'educationStandardsTemplateSave', summary: 'Standards template save', tags: ['Education Standards'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:standards:template:save')]
    public function save(ServiceTemplateSaveRequest $request): Result
    {
        $context = $this->context();
        $data = $request->validated();
        $items = $data['items'] ?? [];
        unset($data['items']);
        $result = $this->service->saveSet($data + ['tenant_id' => $this->tenantId($context), 'campus_id' => $context->currentCampusId]);
        if ($items !== []) {
            $this->service->saveItems($this->tenantId($context), $this->campusId($context), $result['template_set_id'], $items);
        }
        $this->audit($this->events, 'education.standards.template.saved', 'service_template', $result['template_set_id'], $context, $result);

        return $this->success($result);
    }
}
