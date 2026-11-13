<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('category')->index();
            $table->string('hsn_sac', 20)->nullable()->index();
            $table->string('unit', 20)->default('Pcs');
            $table->decimal('default_rate', 12, 2)->default(0.00);
            $table->decimal('gst_rate', 5, 2)->default(18.00);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
