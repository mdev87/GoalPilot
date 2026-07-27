<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_goal_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('week_id')->constrained()->cascadeOnDelete();
            $table->foreignId('goal_id')->constrained()->cascadeOnDelete();
            $table->decimal('priority_percentage', 5, 2)->unsigned();
            $table->timestamps();

            $table->unique(['week_id', 'goal_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_goal_plans');
    }
};
