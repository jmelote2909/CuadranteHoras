<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operator_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->decimal('hours', 4, 2)->default(0);
            $table->timestamps();

            // An operator can only have one shift per day
            $table->unique(['operator_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
