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
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('edu_dict_types', static function (Blueprint $table): void {
            $table->comment('Education dictionary types');
            $table->bigIncrements('id');
            $table->string('owner_type', 20)->comment('system or tenant');
            $table->unsignedBigInteger('tenant_id')->nullable()->comment('Tenant id when owner_type is tenant');
            $table->string('owner_key', 80)->comment('system or tenant:<tenant_id>');
            $table->string('code', 80)->comment('Dictionary code');
            $table->string('name', 120)->comment('Dictionary name');
            $table->string('description', 255)->nullable()->comment('Dictionary description');
            $table->string('status', 20)->default('enabled')->comment('enabled or disabled');
            $table->boolean('is_locked')->default(false)->comment('Locked system dictionary cannot be deleted');
            $table->integer('sort_order')->default(0)->comment('Sort order');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['owner_key', 'code'], 'uk_edu_dict_types_owner_code');
            $table->index(['tenant_id', 'status'], 'idx_edu_dict_types_tenant_status');
            $table->index(['owner_type', 'status'], 'idx_edu_dict_types_owner_status');
            $table->index('deleted_at', 'idx_edu_dict_types_deleted_at');
        });

        Schema::create('edu_dict_items', static function (Blueprint $table): void {
            $table->comment('Education dictionary items');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('dict_type_id')->comment('Dictionary type id');
            $table->string('owner_key', 80)->comment('Copied owner key for lookup');
            $table->string('dict_code', 80)->comment('Copied dictionary code for lookup');
            $table->string('label', 120)->comment('Display label');
            $table->string('value', 120)->comment('Stored value');
            $table->string('color', 40)->nullable()->comment('UI color token');
            $table->json('extra')->nullable()->comment('Extra metadata');
            $table->integer('sort_order')->default(0)->comment('Sort order');
            $table->string('status', 20)->default('enabled')->comment('enabled or disabled');
            $table->boolean('is_default')->default(false)->comment('Default item');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['dict_type_id', 'value'], 'uk_edu_dict_items_type_value');
            $table->index(['owner_key', 'dict_code', 'status'], 'idx_edu_dict_items_owner_code_status');
            $table->index(['dict_type_id', 'status', 'sort_order'], 'idx_edu_dict_items_type_status_sort');
            $table->index('deleted_at', 'idx_edu_dict_items_deleted_at');
        });

        Schema::create('edu_feature_flags', static function (Blueprint $table): void {
            $table->comment('Education feature flags');
            $table->bigIncrements('id');
            $table->string('owner_type', 20)->comment('system or tenant');
            $table->unsignedBigInteger('tenant_id')->nullable()->comment('Tenant id when owner_type is tenant');
            $table->string('owner_key', 80)->comment('system or tenant:<tenant_id>');
            $table->string('feature_code', 120)->comment('Feature code');
            $table->string('feature_name', 120)->comment('Feature display name');
            $table->string('description', 255)->nullable()->comment('Feature description');
            $table->boolean('enabled')->default(false)->comment('Feature enabled value');
            $table->json('config')->nullable()->comment('Feature config JSON');
            $table->timestamp('effective_from')->nullable()->comment('Feature effective start time');
            $table->timestamp('effective_to')->nullable()->comment('Feature effective end time');
            $table->string('status', 20)->default('enabled')->comment('row enabled or disabled');
            $table->boolean('is_locked')->default(false)->comment('Locked system flag cannot be deleted');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['owner_key', 'feature_code'], 'uk_edu_feature_flags_owner_feature');
            $table->index(['tenant_id', 'enabled'], 'idx_edu_feature_flags_tenant_enabled');
            $table->index(['owner_type', 'status'], 'idx_edu_feature_flags_owner_status');
            $table->index(['feature_code', 'status'], 'idx_edu_feature_flags_feature_status');
            $table->index('deleted_at', 'idx_edu_feature_flags_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edu_feature_flags');
        Schema::dropIfExists('edu_dict_items');
        Schema::dropIfExists('edu_dict_types');
    }
};
