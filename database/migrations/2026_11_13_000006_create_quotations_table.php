<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('quotation_number')->unique();
            $table->string('financial_year', 10)->index();
            $table->unsignedInteger('sequence_no')->index();
            $table->unsignedInteger('revision_number')->default(0)->index();
            $table->boolean('is_latest_revision')->default(true)->index();
            $table->foreignId('parent_quotation_id')->nullable()->constrained('quotations')->nullOnDelete();

            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();

            // Client snapshot
            $table->string('client_name');
            $table->text('billing_address');
            $table->text('shipping_address')->nullable();
            $table->string('gstin', 15)->nullable();
            $table->string('state')->default('Assam');
            $table->string('state_code', 2)->default('18');

            // Dates
            $table->date('quotation_date')->index();
            $table->date('valid_until')->index();
            $table->timestamp('accepted_at')->nullable()->index();

            // Status
            $table->string('status')->default('Draft')->index();

            // Financials
            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->decimal('discount_amount', 12, 2)->default(0.00);
            $table->decimal('taxable_amount', 12, 2)->default(0.00);
            $table->boolean('is_interstate')->default(false);
            $table->decimal('cgst_amount', 12, 2)->default(0.00);
            $table->decimal('sgst_amount', 12, 2)->default(0.00);
            $table->decimal('igst_amount', 12, 2)->default(0.00);
            $table->decimal('total_tax', 12, 2)->default(0.00);
            $table->decimal('round_off', 6, 2)->default(0.00);
            $table->decimal('grand_total', 12, 2)->default(0.00)->index();
            $table->text('amount_in_words')->nullable();

            // Terms & Notes
            $table->text('terms_and_conditions')->nullable();
            $table->text('notes')->nullable();

            // Creator
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['financial_year', 'sequence_no', 'revision_number'], 'quotation_fy_seq_rev_unique');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
