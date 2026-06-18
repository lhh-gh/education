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

namespace App\Model\Education\Standards;

use Hyperf\DbConnection\Model\Model;

class EducationTrialLessonStandardItem extends Model
{
    protected ?string $table = 'edu_trial_lesson_standard_items';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'trial_lesson_standard_id',
        'item_name', 'item_content', 'score_weight', 'sort_order',
        'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer',
        'trial_lesson_standard_id' => 'integer', 'score_weight' => 'decimal:4',
        'sort_order' => 'integer', 'created_by' => 'integer', 'updated_by' => 'integer',
        'created_at' => 'datetime', 'updated_at' => 'datetime',
    ];
}
