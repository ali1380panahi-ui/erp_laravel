<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::table('refresh_tokens', function (Blueprint $table) {

            if (!Schema::hasColumn('refresh_tokens', 'session_id')) {
                $table->string('session_id', 64)->nullable()->after('user_id');
            }

            if (!Schema::hasColumn('refresh_tokens', 'status')) {
                $table->enum('status', ['active', 'used', 'revoked', 'expired'])
                    ->default('active')
                    ->after('used');
            }

            if (!Schema::hasColumn('refresh_tokens', 'device_name')) {
                $table->string('device_name')->nullable()->after('status');
            }

            if (!Schema::hasColumn('refresh_tokens', 'browser')) {
                $table->string('browser')->nullable()->after('device_name');
            }

            if (!Schema::hasColumn('refresh_tokens', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('browser');
            }

            if (!Schema::hasColumn('refresh_tokens', 'used_at')) {
                $table->timestamp('used_at')->nullable()->after('used');
            }

            if (!Schema::hasColumn('refresh_tokens', 'revoked_at')) {
                $table->timestamp('revoked_at')->nullable()->after('expires_at');
            }

            $table->index(['user_id', 'session_id']);
            $table->index(['user_id', 'status']);
            $table->index(['token', 'status']);
        });
    }

    public function down()
    {
        Schema::table('refresh_tokens', function (Blueprint $table) {
            $table->dropColumn([
                'session_id', 'status', 'device_name', 'browser',
                'ip_address', 'used_at', 'revoked_at'
            ]);
        });
    }
};
