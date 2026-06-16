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

use App\Model\Education\Academic\EducationNoticeReceipt;
use Carbon\CarbonInterface;
use Hyperf\Swagger\Annotation\Property;
use Hyperf\Swagger\Annotation\Schema;

#[Schema(title: 'EducationNoticeReceiptSchema')]
final class NoticeReceiptSchema implements \JsonSerializable
{
    #[Property(property: 'id', title: 'ID', type: 'int')]
    public ?int $id;

    public function __construct(private readonly array|EducationNoticeReceipt $model) {}

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->value('id'),
            'tenant_id' => $this->value('tenant_id'),
            'campus_id' => $this->value('campus_id'),
            'notice_id' => $this->value('notice_id'),
            'guardian_id' => $this->value('guardian_id'),
            'student_id' => $this->value('student_id'),
            'relation' => $this->value('relation'),
            'guardian_name_snapshot' => $this->value('guardian_name_snapshot'),
            'student_name_snapshot' => $this->value('student_name_snapshot'),
            'status' => $this->value('status'),
            'delivered_at' => $this->formatDate($this->value('delivered_at')),
            'read_at' => $this->formatDate($this->value('read_at')),
            'read_by_profile_id' => $this->value('read_by_profile_id'),
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
