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

namespace App\Schema\Education\Academic;

use App\Model\Education\Academic\EducationNotice;
use Carbon\CarbonInterface;
use Hyperf\Swagger\Annotation\Property;
use Hyperf\Swagger\Annotation\Schema;

#[Schema(title: 'EducationNoticeSchema')]
final class NoticeSchema implements \JsonSerializable
{
    #[Property(property: 'id', title: 'ID', type: 'int')]
    public ?int $id;

    public function __construct(private readonly array|EducationNotice $model) {}

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->value('id'),
            'tenant_id' => $this->value('tenant_id'),
            'campus_id' => $this->value('campus_id'),
            'notice_no' => $this->value('notice_no'),
            'notice_type' => $this->value('notice_type'),
            'target_type' => $this->value('target_type'),
            'target_id' => $this->value('target_id'),
            'title' => $this->value('title'),
            'content' => $this->value('content'),
            'priority' => $this->value('priority'),
            'status' => $this->value('status'),
            'published_at' => $this->formatDate($this->value('published_at')),
            'published_by' => $this->value('published_by'),
            'withdrawn_at' => $this->formatDate($this->value('withdrawn_at')),
            'withdrawn_by' => $this->value('withdrawn_by'),
            'withdraw_reason' => $this->value('withdraw_reason'),
            'expire_at' => $this->formatDate($this->value('expire_at')),
            'receipt_count' => $this->value('receipt_count'),
            'read_count' => $this->value('read_count'),
            'remark' => $this->value('remark'),
            'created_at' => $this->formatDate($this->value('created_at')),
            'updated_at' => $this->formatDate($this->value('updated_at')),
        ];
    }

    private function value(string $key): mixed
    {
        return \is_array($this->model) ? ($this->model[$key] ?? null) : $this->model->{$key};
    }

    private function formatDate(mixed $value): ?string
    {
        return $value instanceof CarbonInterface ? $value->format(CarbonInterface::DEFAULT_TO_STRING_FORMAT) : ($value === null ? null : (string) $value);
    }
}
