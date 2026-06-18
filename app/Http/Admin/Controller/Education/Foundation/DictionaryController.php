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

namespace App\Http\Admin\Controller\Education\Foundation;

use App\Exception\BusinessException;
use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\Education\Foundation\ResolveEducationContextMiddleware;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Education\Foundation\DictItemPageRequest;
use App\Http\Admin\Request\Education\Foundation\DictItemSaveRequest;
use App\Http\Admin\Request\Education\Foundation\DictItemStatusRequest;
use App\Http\Admin\Request\Education\Foundation\DictTypePageRequest;
use App\Http\Admin\Request\Education\Foundation\DictTypeSaveRequest;
use App\Http\Admin\Request\Education\Foundation\DictTypeStatusRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use App\Http\CurrentUser;
use App\Schema\Education\Foundation\DictItemSchema;
use App\Schema\Education\Foundation\DictTypeSchema;
use App\Service\Education\Foundation\DictionaryService;
use App\Service\Education\Foundation\EducationUserContext;
use Hyperf\Context\Context;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Delete;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\JsonContent;
use Hyperf\Swagger\Annotation\Post;
use Hyperf\Swagger\Annotation\Put;
use Hyperf\Swagger\Annotation\RequestBody;
use Mine\Access\Attribute\Permission;
use Mine\Swagger\Attributes\PageResponse;
use Mine\Swagger\Attributes\ResultResponse;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: ResolveEducationContextMiddleware::class, priority: 98)]
#[Middleware(middleware: OperationMiddleware::class, priority: 97)]
final class DictionaryController extends AbstractController
{
    public function __construct(
        private readonly DictionaryService $service,
        private readonly CurrentUser $currentUser
    ) {}

    #[Get(
        path: '/admin/education/foundation/dict-types/page',
        operationId: 'educationDictTypePage',
        summary: 'Dictionary type page',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['Education Foundation'],
    )]
    #[PageResponse(instance: DictTypeSchema::class)]
    #[Permission(code: 'education:foundation:dictionary:page')]
    public function pageTypes(DictTypePageRequest $request): Result
    {
        return $this->success(
            $this->service->pageTypes(
                $request->validated(),
                $this->getCurrentPage(),
                $this->getPageSize(),
                $this->context()
            )
        );
    }

    #[Post(
        path: '/admin/education/foundation/dict-types',
        operationId: 'educationDictTypeCreate',
        summary: 'Create dictionary type',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['Education Foundation'],
    )]
    #[RequestBody(content: new JsonContent(ref: DictTypeSaveRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:foundation:dictionary:create')]
    public function createType(DictTypeSaveRequest $request): Result
    {
        $type = $this->service->createType($request->validated(), $this->context(), $this->currentUser->id());

        return $this->success(['id' => $type->id, 'owner_key' => $type->owner_key]);
    }

    #[Put(
        path: '/admin/education/foundation/dict-types/{id}',
        operationId: 'educationDictTypeUpdate',
        summary: 'Update dictionary type',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['Education Foundation'],
    )]
    #[RequestBody(content: new JsonContent(ref: DictTypeSaveRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:foundation:dictionary:update')]
    public function updateType(int $id, DictTypeSaveRequest $request): Result
    {
        $type = $this->service->updateType($id, $request->validated(), $this->context(), $this->currentUser->id());

        return $this->success(['id' => $type->id]);
    }

    #[Put(
        path: '/admin/education/foundation/dict-types/{id}/status',
        operationId: 'educationDictTypeStatus',
        summary: 'Update dictionary type status',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['Education Foundation'],
    )]
    #[RequestBody(content: new JsonContent(ref: DictTypeStatusRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:foundation:dictionary:status')]
    public function typeStatus(int $id, DictTypeStatusRequest $request): Result
    {
        $type = $this->service->changeTypeStatus($id, $request->validated()['status'], $this->context(), $this->currentUser->id());

        return $this->success(['id' => $type->id, 'status' => $type->status]);
    }

    #[Delete(
        path: '/admin/education/foundation/dict-types/{id}',
        operationId: 'educationDictTypeDelete',
        summary: 'Delete dictionary type',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['Education Foundation'],
    )]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:foundation:dictionary:delete')]
    public function deleteType(int $id): Result
    {
        $this->service->deleteType($id, $this->context());

        return $this->success();
    }

    #[Get(
        path: '/admin/education/foundation/dict-items/page',
        operationId: 'educationDictItemPage',
        summary: 'Dictionary item page',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['Education Foundation'],
    )]
    #[PageResponse(instance: DictItemSchema::class)]
    #[Permission(code: 'education:foundation:dictionary-item:page')]
    public function pageItems(DictItemPageRequest $request): Result
    {
        return $this->success(
            $this->service->pageItems(
                $request->validated(),
                $this->getCurrentPage(),
                $this->getPageSize(),
                $this->context()
            )
        );
    }

    #[Post(
        path: '/admin/education/foundation/dict-items',
        operationId: 'educationDictItemCreate',
        summary: 'Create dictionary item',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['Education Foundation'],
    )]
    #[RequestBody(content: new JsonContent(ref: DictItemSaveRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:foundation:dictionary-item:create')]
    public function createItem(DictItemSaveRequest $request): Result
    {
        $item = $this->service->createItem($request->validated(), $this->context(), $this->currentUser->id());

        return $this->success(['id' => $item->id, 'dict_type_id' => $item->dict_type_id]);
    }

    #[Put(
        path: '/admin/education/foundation/dict-items/{id}',
        operationId: 'educationDictItemUpdate',
        summary: 'Update dictionary item',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['Education Foundation'],
    )]
    #[RequestBody(content: new JsonContent(ref: DictItemSaveRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:foundation:dictionary-item:update')]
    public function updateItem(int $id, DictItemSaveRequest $request): Result
    {
        $item = $this->service->updateItem($id, $request->validated(), $this->context(), $this->currentUser->id());

        return $this->success(['id' => $item->id]);
    }

    #[Put(
        path: '/admin/education/foundation/dict-items/{id}/status',
        operationId: 'educationDictItemStatus',
        summary: 'Update dictionary item status',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['Education Foundation'],
    )]
    #[RequestBody(content: new JsonContent(ref: DictItemStatusRequest::class))]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:foundation:dictionary-item:status')]
    public function itemStatus(int $id, DictItemStatusRequest $request): Result
    {
        $item = $this->service->changeItemStatus($id, $request->validated()['status'], $this->context(), $this->currentUser->id());

        return $this->success(['id' => $item->id, 'status' => $item->status]);
    }

    #[Delete(
        path: '/admin/education/foundation/dict-items/{id}',
        operationId: 'educationDictItemDelete',
        summary: 'Delete dictionary item',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['Education Foundation'],
    )]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:foundation:dictionary-item:delete')]
    public function deleteItem(int $id): Result
    {
        $this->service->deleteItem($id, $this->context());

        return $this->success();
    }

    #[Get(
        path: '/admin/education/foundation/dictionaries/{code}/items',
        operationId: 'educationDictionaryItems',
        summary: 'Dictionary item lookup',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['Education Foundation'],
    )]
    #[ResultResponse(instance: new Result())]
    #[Permission(code: 'education:foundation:dictionary-item:lookup')]
    public function items(string $code): Result
    {
        return $this->success([
            'dict_code' => $code,
            'items' => $this->service->items($code, $this->context()->tenantId),
        ]);
    }

    private function context(): EducationUserContext
    {
        $context = Context::get(ResolveEducationContextMiddleware::CONTEXT_KEY);
        if (! $context instanceof EducationUserContext) {
            throw new BusinessException(ResultCode::FORBIDDEN, 'education user context is missing');
        }

        return $context;
    }
}
