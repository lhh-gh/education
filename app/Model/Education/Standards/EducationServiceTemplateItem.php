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

class EducationServiceTemplateItem extends Model
{
    protected ?string $table = 'edu_service_template_items';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'template_set_id', 'item_type',
        'item_title', 'item_content', 'sort_order', 'created_by', 'updated_by',
        'created_at', 'updated_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer',
        'template_set_id' => 'integer', 'sort_order' => 'integer',
        'created_by' => 'integer', 'updated_by' => 'integer',
        'created_at' => 'datetime', 'updated_at' => 'datetime',
    ];
}
