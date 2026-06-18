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

class EducationCourseStageGoalAbilityRelation extends Model
{
    protected ?string $table = 'edu_course_stage_goal_ability_relations';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'stage_goal_id', 'ability_point_id',
        'weight', 'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer',
        'stage_goal_id' => 'integer', 'ability_point_id' => 'integer',
        'weight' => 'decimal:4', 'created_by' => 'integer', 'updated_by' => 'integer',
        'created_at' => 'datetime', 'updated_at' => 'datetime',
    ];
}
