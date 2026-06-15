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

namespace App\Model\Education\Academic;

use App\Model\Education\Foundation\EducationCampus;
use App\Model\Education\Foundation\EducationTenant;
use Hyperf\Database\Model\Relations\BelongsTo;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class EducationLessonPackage extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_lesson_packages';

    protected array $fillable = [
        'id',
        'tenant_id',
        'campus_id',
        'course_id',
        'code',
        'name',
        'lesson_units',
        'bonus_units',
        'total_units',
        'list_price',
        'sale_price',
        'validity_days',
        'status',
        'sort_order',
        'remark',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'course_id' => 'integer',
        'lesson_units' => 'decimal:2',
        'bonus_units' => 'decimal:2',
        'total_units' => 'decimal:2',
        'list_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'validity_days' => 'integer',
        'sort_order' => 'integer',
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

    public function campus(): BelongsTo
    {
        return $this->belongsTo(EducationCampus::class, 'campus_id', 'id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(EducationCourse::class, 'course_id', 'id');
    }
}
