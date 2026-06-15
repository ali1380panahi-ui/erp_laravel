<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            
            if (!Schema::hasColumn('personal_access_tokens', 'session_id')) {
                $table->string('session_id', 64)->nullable()->after('token');
            }
            
            if (!Schema::hasColumn('personal_access_tokens', 'device_name')) {
                $table->string('device_name')->nullable()->after('session_id');
            }
            
            if (!Schema::hasColumn('personal_access_tokens', 'browser')) {
                $table->string('browser')->nullable()->after('device_name');
            }
            
            if (!Schema::hasColumn('personal_access_tokens', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('browser');
            }
            
            if (!Schema::hasColumn('personal_access_tokens', 'revoked_at')) {
                $table->timestamp('revoked_at')->nullable()->after('last_used_at');
            }
            
            if (!Schema::hasColumn('personal_access_tokens', 'revoked_reason')) {
                $table->string('revoked_reason')->nullable()->after('revoked_at');
            }
        });
        
        
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $indexes = $this->getIndexes('personal_access_tokens');
            
            if (!in_array('personal_access_tokens_tokenable_id_session_id_index', $indexes)) {
                $table->index(['tokenable_id', 'session_id']);
            }
            
            if (!in_array('personal_access_tokens_tokenable_id_revoked_at_index', $indexes)) {
                $table->index(['tokenable_id', 'revoked_at']);
            }
        });
    }

    public function down()
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->dropColumn([
                'session_id', 'device_name', 'browser', 
                'ip_address', 'revoked_at', 'revoked_reason'
            ]);
        });
    }
    
    private function getIndexes(string $table): array
    {
        $connection = Schema::getConnection();
        $databaseName = $connection->getDatabaseName();
        $tableName = $table;
        
        $indexes = $connection->select("
            SELECT DISTINCT INDEX_NAME 
            FROM INFORMATION_SCHEMA.STATISTICS 
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
        ", [$databaseName, $tableName]);
        
        return array_column($indexes, 'INDEX_NAME');
    }
};