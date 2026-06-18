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

namespace App\Model\Education\Growth;

use App\Model\Enums\Education\Growth\CampaignStatus;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;

class EducationGrowthCampaign extends Model
{
    use SoftDeletes;

    protected ?string $table = 'edu_growth_campaigns';

    protected array $fillable = [
        'id', 'tenant_id', 'campus_id', 'campaign_code', 'campaign_name',
        'source_id', 'start_date', 'end_date', 'budget_cents', 'status',
        'remark', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
    ];

    protected array $casts = [
        'id' => 'integer', 'tenant_id' => 'integer', 'campus_id' => 'integer',
        'source_id' => 'integer', 'start_date' => 'date', 'end_date' => 'date',
        'budget_cents' => 'integer', 'status' => CampaignStatus::class,
        'created_by' => 'integer', 'updated_by' => 'integer',
        'created_at' => 'datetime', 'updated_at' => 'datetime', 'deleted_at' => 'datetime',
    ];
}
