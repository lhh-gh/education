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

use App\Model\Enums\Education\Foundation\EducationRoleCode;
use App\Model\Enums\Education\Foundation\UserProfileStatus;
use App\Model\Permission\User;
use Hyperf\Database\Model\Relations\BelongsTo;
use Hyperf\Database\Model\Relations\HasMany;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class EducationUserProfile extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_user_profiles';

    protected array $fillable = [
        'id',
        'profile_key',
        'tenant_id',
        'user_id',
        'role_code',
        'display_name',
        'mobile',
        'avatar',
        'openid',
        'unionid',
        'status',
        'current_campus_id',
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
        'user_id' => 'integer',
        'role_code' => EducationRoleCode::class,
        'status' => UserProfileStatus::class,
        'current_campus_id' => 'integer',
        'settings' => 'array',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(EducationTenant::class, 'tenant_id', 'id');
    }

    public function currentCampus(): BelongsTo
    {
        return $this->belongsTo(EducationCampus::class, 'current_campus_id', 'id');
    }

    public function campusScopes(): HasMany
    {
        return $this->hasMany(EducationUserCampusScope::class, 'user_profile_id', 'id');
    }
}
