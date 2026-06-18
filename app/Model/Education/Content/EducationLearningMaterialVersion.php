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

use App\Model\Enums\Education\Content\ContentPublishStatus;
use Hyperf\DbConnection\Model\Model;

class EducationLearningMaterialVersion extends Model
{
    protected ?string $table = 'edu_learning_material_versions';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'material_id', 'version_no', 'title',
        'content', 'status', 'snapshot_json', 'published_at', 'created_by',
        'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer',
        'material_id' => 'integer', 'version_no' => 'integer',
        'status' => ContentPublishStatus::class, 'snapshot_json' => 'array',
        'published_at' => 'datetime', 'created_by' => 'integer',
        'updated_by' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime',
    ];
}
