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
use App\Model\Education\Foundation\EducationUserProfile;
use Hyperf\Database\Model\Relations\BelongsTo;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class EducationTeacher extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_teachers';

    protected array $fillable = [
        'id',
        'tenant_id',
        'campus_id',
        'user_profile_id',
        'teacher_no',
        'name',
        'mobile',
        'gender',
        'birthday',
        'title',
        'hire_date',
        'avatar',
        'introduction',
        'status',
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
        'user_profile_id' => 'integer',
        'birthday' => 'date',
        'hire_date' => 'date',
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

    public function userProfile(): BelongsTo
    {
        return $this->belongsTo(EducationUserProfile::class, 'user_profile_id', 'id');
    }
}
