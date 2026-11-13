<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follow_ups', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->foreignId('assigned_user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('client_id')
                ->nullable()
                ->constrained('clients')
                ->nullOnDelete();
            $table->foreignId('prospect_id')
                ->nullable()
                ->constrained('prospects')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('quotation_id')->nullable()->index();
            $table->date('due_date')->index();
            $table->time('due_time')->nullable();
            $table->string('status')->default('Pending')->index();
            $table->text('outcome_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follow_ups');
    }
};
