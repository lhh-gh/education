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
        Schema::create('edu_user_profiles', static function (Blueprint $table): void {
            $table->comment('Education user profiles');
            $table->bigIncrements('id');
            $table->string('profile_key', 120)->comment('Unique profile key, platform:user or tenant:tenant:user');
            $table->unsignedBigInteger('tenant_id')->nullable()->comment('Tenant id, null for platform profiles');
            $table->unsignedBigInteger('user_id')->comment('MineAdmin user id');
            $table->string('role_code', 40)->comment('Education role code');
            $table->string('display_name', 80)->comment('Education display name');
            $table->string('mobile', 30)->nullable()->comment('Education contact mobile');
            $table->string('avatar', 255)->nullable()->comment('Education avatar');
            $table->string('openid', 80)->nullable()->comment('WeChat openid');
            $table->string('unionid', 80)->nullable()->comment('WeChat unionid');
            $table->string('status', 20)->default('enabled')->comment('enabled or disabled');
            $table->unsignedBigInteger('current_campus_id')->nullable()->comment('Default campus id');
            $table->json('settings')->nullable()->comment('Education profile settings');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();
            $table->softDeletes();

            $table->unique('profile_key', 'uk_edu_user_profiles_profile_key');
            $table->index(['tenant_id', 'user_id'], 'idx_edu_user_profiles_tenant_user');
            $table->index(['tenant_id', 'role_code'], 'idx_edu_user_profiles_tenant_role');
            $table->index(['tenant_id', 'status'], 'idx_edu_user_profiles_tenant_status');
            $table->index('user_id', 'idx_edu_user_profiles_user');
            $table->index('openid', 'idx_edu_user_profiles_openid');
            $table->index('unionid', 'idx_edu_user_profiles_unionid');
            $table->index('deleted_at', 'idx_edu_user_profiles_deleted_at');
        });

        Schema::create('edu_user_campus_scopes', static function (Blueprint $table): void {
            $table->comment('Education user campus scopes');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant id');
            $table->unsignedBigInteger('user_profile_id')->comment('Education user profile id');
            $table->unsignedBigInteger('user_id')->comment('MineAdmin user id');
            $table->unsignedBigInteger('campus_id')->comment('Campus id');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Updater user id');
            $table->datetimes();

            $table->unique(['tenant_id', 'user_id', 'campus_id'], 'uk_edu_user_campus_scopes_tenant_user_campus');
            $table->index(['tenant_id', 'user_profile_id'], 'idx_edu_user_campus_scopes_profile');
            $table->index(['tenant_id', 'campus_id'], 'idx_edu_user_campus_scopes_campus');
            $table->index('user_id', 'idx_edu_user_campus_scopes_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edu_user_campus_scopes');
        Schema::dropIfExists('edu_user_profiles');
    }
};
