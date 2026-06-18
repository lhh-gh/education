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

namespace App\Model\Education\Content;

use App\Model\Enums\Education\Content\ContentReviewStatus;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class EducationContentReviewRecord extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_content_review_records';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'business_type', 'business_id',
        'reviewer_id', 'status', 'review_note', 'reviewed_at', 'created_by',
        'updated_by', 'created_at', 'updated_at', 'deleted_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer',
        'business_id' => 'integer', 'reviewer_id' => 'integer',
        'status' => ContentReviewStatus::class, 'reviewed_at' => 'datetime',
        'created_by' => 'integer', 'updated_by' => 'integer',
        'created_at' => 'datetime', 'updated_at' => 'datetime', 'deleted_at' => 'datetime',
    ];
}
