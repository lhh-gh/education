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

class CreateEducationTenantCampusTables extends Migration
{
    public function up(): void
    {
        Schema::create('edu_tenants', static function (Blueprint $table): void {
            $table->comment('Education tenants');
            $table->bigIncrements('id');
            $table->string('name', 120)->comment('Tenant full name');
            $table->string('code', 64)->comment('Tenant unique code');
            $table->string('short_name', 60)->nullable()->comment('Tenant short name');
            $table->string('contact_name', 60)->nullable()->comment('Primary contact name');
            $table->string('contact_phone', 30)->nullable()->comment('Primary contact phone');
            $table->string('status', 20)->default('enabled')->comment('enabled or disabled');
            $table->json('settings')->nullable()->comment('Tenant settings JSON');
            $table->timestamp('enabled_at')->nullable()->comment('Enabled time');
            $table->timestamp('disabled_at')->nullable()->comment('Disabled time');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique('code', 'uk_edu_tenants_code');
            $table->index('status', 'idx_edu_tenants_status');
            $table->index('name', 'idx_edu_tenants_name');
            $table->index('deleted_at', 'idx_edu_tenants_deleted_at');
        });

        Schema::create('edu_campuses', static function (Blueprint $table): void {
            $table->comment('Education campuses');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->string('name', 120)->comment('Campus name');
            $table->string('code', 64)->comment('Campus code inside tenant');
            $table->string('contact_name', 60)->nullable()->comment('Campus contact name');
            $table->string('contact_phone', 30)->nullable()->comment('Campus contact phone');
            $table->string('address', 255)->nullable()->comment('Campus address');
            $table->string('status', 20)->default('enabled')->comment('enabled or disabled');
            $table->json('settings')->nullable()->comment('Campus settings JSON');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique(['tenant_id', 'code'], 'uk_edu_campuses_tenant_code');
            $table->index(['tenant_id', 'status'], 'idx_edu_campuses_tenant_status');
            $table->index(['tenant_id', 'name'], 'idx_edu_campuses_tenant_name');
            $table->index('deleted_at', 'idx_edu_campuses_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edu_campuses');
        Schema::dropIfExists('edu_tenants');
    }
}
