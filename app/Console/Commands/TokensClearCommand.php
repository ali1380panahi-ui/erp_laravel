<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TokensClearCommand extends Command
{
    protected $signature = 'tokens:clear';
    protected $description = 'Truncate personal_access_tokens and refresh_tokens tables (reset auto_increment)';

    public function handle()
    {
        // بررسی وجود جدول اول
        if (!Schema::hasTable('personal_access_tokens')) {
            $this->error('Table personal_access_tokens does not exist!');
            return 1;
        }

        // غیرفعال کردن موقت کلیدهای خارجی
        Schema::disableForeignKeyConstraints();

        // ترانکیت جدول توکن‌های اصلی
        DB::table('personal_access_tokens')->truncate();
        $this->info('✓ personal_access_tokens truncated.');

        // اگر جدول refresh_tokens وجود داشته باشد، آن را هم ترانکیت کن
        if (Schema::hasTable('refresh_tokens')) {
            DB::table('refresh_tokens')->truncate();
            $this->info('✓ refresh_tokens truncated.');
        } else {
            $this->warn('refresh_tokens table not found (skipped).');
        }

        // فعال کردن مجدد کلیدهای خارجی
        Schema::enableForeignKeyConstraints();

        $this->newLine();
        $this->info('All token tables have been cleared properly.');
        return 0;
    }
}