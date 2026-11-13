<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->index();
            $table->string('contact_person')->index();
            $table->string('phone')->index();
            $table->string('email')->nullable()->index();
            $table->string('gstin', 15)->nullable()->index();
            $table->text('billing_address');
            $table->text('shipping_address')->nullable();
            $table->string('state')->default('Assam');
            $table->string('state_code', 2)->default('18')->index();
            $table->foreignId('assigned_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
