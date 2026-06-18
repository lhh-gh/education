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

class EducationLessonCommentTagRelation extends Model
{
    protected ?string $table = 'edu_lesson_comment_tag_relations';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'lesson_comment_id', 'performance_tag_id', 'student_id',
        'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'lesson_comment_id' => 'integer',
        'performance_tag_id' => 'integer',
        'student_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
