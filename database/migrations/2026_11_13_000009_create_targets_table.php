<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('period_type', 20)->default('monthly');
            $table->string('financial_year', 10)->index();
            $table->unsignedTinyInteger('month')->default(0)->index();
            $table->decimal('target_amount', 14, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'period_type', 'financial_year', 'month'], 'targets_user_period_fy_month_unique');
            $table->index(['financial_year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('targets');
    }
};
