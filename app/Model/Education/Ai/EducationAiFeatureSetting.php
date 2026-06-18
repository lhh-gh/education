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

use App\Model\Enums\Education\Ai\AiSafetyLevel;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class EducationAiFeatureSetting extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_ai_feature_settings';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'feature_code', 'feature_name', 'model_config_id', 'enabled',
        'review_required', 'safety_level', 'config_json', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer', 'model_config_id' => 'integer',
        'enabled' => 'boolean', 'review_required' => 'boolean', 'safety_level' => AiSafetyLevel::class, 'config_json' => 'array',
        'created_by' => 'integer', 'updated_by' => 'integer',
        'created_at' => 'datetime', 'updated_at' => 'datetime', 'deleted_at' => 'datetime',
    ];
}
