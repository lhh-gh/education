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

namespace App\Model\Education\Operations;

use Hyperf\DbConnection\Model\Model;

class EducationLessonChangeLog extends Model
{
    protected ?string $table = 'edu_lesson_change_logs';

    protected array $fillable = [
        'id',
        'tenant_id',
        'campus_id',
        'lesson_id',
        'change_request_id',
        'change_type',
        'before_json',
        'after_json',
        'operator_id',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'lesson_id' => 'integer',
        'change_request_id' => 'integer',
        'before_json' => 'array',
        'after_json' => 'array',
        'operator_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
