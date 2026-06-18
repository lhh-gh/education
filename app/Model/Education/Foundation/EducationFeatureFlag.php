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

namespace App\Model\Education\Foundation;

use Hyperf\Database\Model\Relations\BelongsTo;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class EducationFeatureFlag extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_feature_flags';

    protected array $fillable = [
        'id',
        'owner_type',
        'tenant_id',
        'owner_key',
        'feature_code',
        'feature_name',
        'description',
        'enabled',
        'config',
        'effective_from',
        'effective_to',
        'status',
        'is_locked',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'enabled' => 'boolean',
        'config' => 'array',
        'effective_from' => 'datetime',
        'effective_to' => 'datetime',
        'is_locked' => 'boolean',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(EducationTenant::class, 'tenant_id', 'id');
    }
}
