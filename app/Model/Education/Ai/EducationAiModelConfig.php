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

class EducationAiModelConfig extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_ai_model_configs';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'config_code', 'provider', 'model_name', 'api_key_ciphertext',
        'base_url', 'status', 'default_temperature', 'daily_token_limit', 'created_by', 'updated_by',
        'created_at', 'updated_at', 'deleted_at',
    ];

    protected array $hidden = ['api_key_ciphertext'];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer',
        'default_temperature' => 'decimal:2', 'daily_token_limit' => 'integer',
        'created_by' => 'integer', 'updated_by' => 'integer',
        'created_at' => 'datetime', 'updated_at' => 'datetime', 'deleted_at' => 'datetime',
    ];
}
