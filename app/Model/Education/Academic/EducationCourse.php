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
use Hyperf\Database\Model\Relations\HasMany;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class EducationCourse extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_courses';

    protected array $fillable = [
        'id',
        'tenant_id',
        'campus_id',
        'code',
        'name',
        'category',
        'subject',
        'unit_minutes',
        'cover_url',
        'description',
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
        'unit_minutes' => 'integer',
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

    public function lessonPackages(): HasMany
    {
        return $this->hasMany(EducationLessonPackage::class, 'course_id', 'id');
    }

    public function teacherCourses(): HasMany
    {
        return $this->hasMany(EducationTeacherCourse::class, 'course_id', 'id');
    }
}
