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

namespace HyperfTests\Feature\Education\Foundation;

use Hyperf\Database\Schema\Schema;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class DictionaryFeatureMigrationTest extends TestCase
{
    public function testDictionaryAndFeatureTablesHaveRequiredColumnsAndIndexes(): void
    {
        self::assertTrue(Schema::hasTable('edu_dict_types'));
        self::assertTrue(Schema::hasTable('edu_dict_items'));
        self::assertTrue(Schema::hasTable('edu_feature_flags'));

        foreach (['owner_type', 'tenant_id', 'owner_key', 'code', 'status', 'is_locked', 'deleted_at'] as $column) {
            self::assertTrue(Schema::hasColumn('edu_dict_types', $column), "edu_dict_types missing {$column}");
        }

        foreach (['dict_type_id', 'owner_key', 'dict_code', 'label', 'value', 'status', 'is_default', 'deleted_at'] as $column) {
            self::assertTrue(Schema::hasColumn('edu_dict_items', $column), "edu_dict_items missing {$column}");
        }

        foreach (['owner_type', 'tenant_id', 'owner_key', 'feature_code', 'enabled', 'config', 'effective_from', 'effective_to', 'status', 'deleted_at'] as $column) {
            self::assertTrue(Schema::hasColumn('edu_feature_flags', $column), "edu_feature_flags missing {$column}");
        }

        self::assertTrue($this->hasIndex('edu_dict_types', 'uk_edu_dict_types_owner_code'));
        self::assertTrue($this->hasIndex('edu_dict_items', 'uk_edu_dict_items_type_value'));
        self::assertTrue($this->hasIndex('edu_feature_flags', 'uk_edu_feature_flags_owner_feature'));
    }

    private function hasIndex(string $table, string $index): bool
    {
        $indexes = Schema::getConnection()
            ->select('SHOW INDEX FROM `' . $table . '` WHERE Key_name = ?', [$index]);

        return $indexes !== [];
    }
}
