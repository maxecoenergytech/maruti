<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prospects', function (Blueprint $table) {
            $table->id();
            $table->string('contact_person')->index();
            $table->string('company_name')->nullable()->index();
            $table->string('phone')->index();
            $table->string('email')->nullable();
            $table->string('source')->default('Walk-in')->index();
            $table->text('requirement');
            $table->decimal('expected_value', 12, 2)->default(0.00);
            $table->string('stage')->default('New')->index();
            $table->text('lost_reason')->nullable();
            $table->date('next_follow_up_date')->nullable()->index();
            $table->foreignId('assigned_user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('converted_client_id')
                ->nullable()
                ->constrained('clients')
                ->nullOnDelete();
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prospects');
    }
};
