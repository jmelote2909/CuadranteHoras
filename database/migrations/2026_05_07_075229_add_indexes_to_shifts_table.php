<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            // Speeds up monthly queries per operator (the most common query pattern)
            $table->index(['operator_id', 'date'], 'idx_shifts_operator_date');
            // Speeds up Días Amarillos queries filtering by color
            $table->index(['color', 'operator_id'], 'idx_shifts_color_operator');
        });
    }

    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropIndex('idx_shifts_operator_date');
            $table->dropIndex('idx_shifts_color_operator');
        });
    }
};
