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

namespace App\Model\Education\Admissions;

use Hyperf\DbConnection\Model\Model;

class EducationLeadConversionRecord extends Model
{
    protected ?string $table = 'edu_lead_conversion_records';

    protected array $fillable = [
        'id',
        'tenant_id',
        'campus_id',
        'lead_id',
        'student_id',
        'guardian_id',
        'enrollment_id',
        'student_course_account_id',
        'status',
        'converted_by',
        'converted_at',
        'payload_json',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'lead_id' => 'integer',
        'student_id' => 'integer',
        'guardian_id' => 'integer',
        'enrollment_id' => 'integer',
        'student_course_account_id' => 'integer',
        'converted_by' => 'integer',
        'converted_at' => 'datetime',
        'payload_json' => 'array',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
