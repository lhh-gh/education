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

namespace App\Model\Education\Ai;

use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class EducationAiMetricCatalog extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_ai_metric_catalogs';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'metric_code', 'metric_name', 'metric_group', 'query_key',
        'allowed_roles_json', 'status', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer', 'allowed_roles_json' => 'array',
        'created_by' => 'integer', 'updated_by' => 'integer', 'created_at' => 'datetime',
        'updated_at' => 'datetime', 'deleted_at' => 'datetime',
    ];
}
