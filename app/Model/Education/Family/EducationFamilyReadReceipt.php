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

use Hyperf\DbConnection\Model\Model;

class EducationFamilyReadReceipt extends Model
{
    protected ?string $table = 'edu_family_read_receipts';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'business_type', 'business_id', 'student_id', 'reader_type',
        'reader_user_id', 'read_at', 'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'business_id' => 'integer',
        'student_id' => 'integer',
        'reader_user_id' => 'integer',
        'read_at' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
