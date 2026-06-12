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

use Hyperf\Database\Model\Relations\BelongsTo;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class EducationDictItem extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_dict_items';

    protected array $fillable = [
        'id',
        'dict_type_id',
        'owner_key',
        'dict_code',
        'label',
        'value',
        'color',
        'extra',
        'sort_order',
        'status',
        'is_default',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'dict_type_id' => 'integer',
        'extra' => 'array',
        'sort_order' => 'integer',
        'is_default' => 'boolean',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(EducationDictType::class, 'dict_type_id', 'id');
    }
}
