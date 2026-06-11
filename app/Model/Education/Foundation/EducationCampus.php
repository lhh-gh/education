<?php

declare(strict_types=1);

namespace App\Model\Education\Foundation;

use Hyperf\Database\Model\Relations\BelongsTo;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class EducationCampus extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_campuses';

    protected array $fillable = [
        'id',
        'tenant_id',
        'name',
        'code',
        'contact_name',
        'contact_phone',
        'address',
        'status',
        'settings',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'settings' => 'array',
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
