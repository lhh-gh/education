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
        Schema::create('edu_audit_logs', static function (Blueprint $table): void {
            $table->comment('Education domain audit logs');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->nullable()->comment('Tenant id; null for platform-wide audit');
            $table->unsignedBigInteger('campus_id')->nullable()->comment('Campus id; null for tenant-wide audit');
            $table->unsignedBigInteger('actor_user_id')->nullable()->comment('MineAdmin user id that caused the write');
            $table->string('actor_type', 20)->default('admin')->comment('admin, teacher, guardian, or system');
            $table->string('actor_role_code', 80)->nullable()->comment('Education role code from user context');
            $table->string('module', 60)->comment('Product module');
            $table->string('resource', 80)->comment('Resource name');
            $table->string('action', 120)->comment('Full action code');
            $table->string('business_type', 80)->comment('Business object type');
            $table->string('business_id', 80)->nullable()->comment('Business object id');
            $table->string('request_id', 80)->nullable()->comment('Request correlation id');
            $table->string('ip_address', 64)->nullable()->comment('Client IP');
            $table->string('user_agent', 512)->nullable()->comment('Client user agent');
            $table->string('method', 10)->nullable()->comment('HTTP method');
            $table->string('path', 255)->nullable()->comment('HTTP path');
            $table->string('summary', 255)->nullable()->comment('Readable audit summary');
            $table->json('before_snapshot')->nullable()->comment('Sanitized data before write');
            $table->json('after_snapshot')->nullable()->comment('Sanitized data after write');
            $table->json('diff')->nullable()->comment('Sanitized changed keys');
            $table->json('metadata')->nullable()->comment('Extra metadata');
            $table->timestamp('created_at')->nullable()->comment('Audit create time');

            $table->index(['tenant_id', 'created_at'], 'idx_edu_audit_logs_tenant_created');
            $table->index(['tenant_id', 'module', 'created_at'], 'idx_edu_audit_logs_tenant_module_created');
            $table->index(['tenant_id', 'action', 'created_at'], 'idx_edu_audit_logs_tenant_action_created');
            $table->index(['tenant_id', 'actor_user_id', 'created_at'], 'idx_edu_audit_logs_tenant_actor_created');
            $table->index(['tenant_id', 'campus_id', 'created_at'], 'idx_edu_audit_logs_campus_created');
            $table->index(['business_type', 'business_id'], 'idx_edu_audit_logs_business');
            $table->index('request_id', 'idx_edu_audit_logs_request');
            $table->index('created_at', 'idx_edu_audit_logs_created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edu_audit_logs');
    }
};
