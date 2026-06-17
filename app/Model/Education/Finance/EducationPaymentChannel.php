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

namespace App\Model\Education\Finance;

use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class EducationPaymentChannel extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_payment_channels';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'channel_code', 'channel_name', 'channel_type',
        'config_json', 'status', 'sort_order', 'created_by', 'updated_by', 'created_at',
        'updated_at', 'deleted_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'campus_id' => 'integer',
        'config_json' => 'array',
        'sort_order' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
