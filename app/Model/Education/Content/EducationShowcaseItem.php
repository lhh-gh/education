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

namespace App\Model\Education\Content;

use Hyperf\DbConnection\Model\Model;

class EducationShowcaseItem extends Model
{
    protected ?string $table = 'edu_showcase_items';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'showcase_id', 'item_type',
        'student_work_id', 'material_id', 'title', 'content', 'sort_order',
        'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer',
        'showcase_id' => 'integer', 'student_work_id' => 'integer',
        'material_id' => 'integer', 'sort_order' => 'integer',
        'created_by' => 'integer', 'updated_by' => 'integer',
        'created_at' => 'datetime', 'updated_at' => 'datetime',
    ];
}
