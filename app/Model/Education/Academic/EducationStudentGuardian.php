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

use App\Model\Education\Foundation\EducationTenant;
use Hyperf\Database\Model\Relations\BelongsTo;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class EducationStudentGuardian extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_student_guardians';

    protected array $fillable = [
        'id',
        'tenant_id',
        'student_id',
        'guardian_id',
        'relation',
        'is_primary',
        'can_receive_notice',
        'can_submit_leave',
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
        'student_id' => 'integer',
        'guardian_id' => 'integer',
        'is_primary' => 'boolean',
        'can_receive_notice' => 'boolean',
        'can_submit_leave' => 'boolean',
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

    public function student(): BelongsTo
    {
        return $this->belongsTo(EducationStudent::class, 'student_id', 'id');
    }

    public function guardian(): BelongsTo
    {
        return $this->belongsTo(EducationGuardian::class, 'guardian_id', 'id');
    }
}
