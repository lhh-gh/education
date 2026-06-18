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

namespace App\Model\Education\Family;

use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class EducationFamilyServiceAttachment extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_family_service_attachments';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'business_type', 'business_id', 'student_id', 'file_name',
        'file_url', 'file_size', 'uploaded_by', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'business_id' => 'integer',
        'student_id' => 'integer',
        'file_size' => 'integer',
        'uploaded_by' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
