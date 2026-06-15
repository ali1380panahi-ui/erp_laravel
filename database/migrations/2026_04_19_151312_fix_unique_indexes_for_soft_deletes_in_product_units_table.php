<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_units', function (Blueprint $table) {
            $table->dropUniqueIfExists('product_units_title_unique');
            $table->dropUniqueIfExists('product_units_coding_unique');

            $table->dropUniqueIfExists('product_units_title_coding_unique');
            
            $table->unique(['title', 'deleted_at'], 'product_units_title_unique');
            $table->unique(['coding', 'deleted_at'], 'product_units_coding_unique');
        });
    }

    public function down(): void
    {
        Schema::table('product_units', function (Blueprint $table) {
            $table->dropUniqueIfExists('product_units_title_unique');
            $table->dropUniqueIfExists('product_units_coding_unique');
            $table->unique(['title', 'coding'], 'product_units_title_coding_unique');
        });
    }
};