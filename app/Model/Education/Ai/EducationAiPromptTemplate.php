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

class EducationAiPromptTemplate extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_ai_prompt_templates';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'template_code', 'feature_code', 'template_name', 'version',
        'system_prompt', 'user_prompt', 'status', 'published_at', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer', 'version' => 'integer',
        'published_at' => 'datetime', 'created_by' => 'integer', 'updated_by' => 'integer',
        'created_at' => 'datetime', 'updated_at' => 'datetime', 'deleted_at' => 'datetime',
    ];
}
