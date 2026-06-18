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

namespace App\Http\Admin\Controller\Education\Finance;

use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\Education\Foundation\ResolveEducationContextMiddleware;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Education\Finance\FinanceOrderPageRequest;
use App\Http\Admin\Request\Education\Finance\ReceiptIssueRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Education\Finance\ReceiptService;
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
final class ReceiptController extends AbstractController
{
    use FinanceControllerTrait;

    public function __construct(
        private readonly ReceiptService $service,
        private readonly EventDispatcherInterface $events
    ) {}

    #[Get(path: '/admin/education/finance/receipts/page', operationId: 'educationFinanceReceiptPage', summary: 'Receipt page', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Finance'])]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'education:finance:receipt:page')]
    public function page(FinanceOrderPageRequest $request): Result
    {
        return $this->success($this->service->page($request->validated(), $this->context()));
    }

    #[Post(path: '/admin/education/finance/receipts', operationId: 'educationFinanceReceiptIssue', summary: 'Receipt issue', security: [['Bearer' => [], 'ApiKey' => []]], tags: ['Education Finance'])]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:finance:receipt:issue')]
    public function issue(ReceiptIssueRequest $request): Result
    {
        $context = $this->context();
        $result = $this->service->issue($request->validated(), $context);
        $this->audit($this->events, 'education.finance.receipt.issued', 'receipt', $result['id'] ?? null, $context, $result);

        return $this->success($result);
    }
}
