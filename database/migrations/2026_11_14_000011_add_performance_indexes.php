<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->index(['is_latest_revision', 'status'], 'idx_quotes_latest_status');
            $table->index(['is_latest_revision', 'quotation_date'], 'idx_quotes_latest_date');
            $table->index(['is_latest_revision', 'accepted_at'], 'idx_quotes_latest_accepted');
            $table->index(['created_by_user_id', 'is_latest_revision'], 'idx_quotes_creator_latest');
        });

        Schema::table('prospects', function (Blueprint $table) {
            $table->index(['stage', 'expected_value'], 'idx_prospects_stage_value');
            $table->index(['assigned_user_id', 'stage'], 'idx_prospects_user_stage');
        });

        Schema::table('follow_ups', function (Blueprint $table) {
            $table->index(['status', 'due_date'], 'idx_followups_status_due');
            $table->index(['assigned_user_id', 'status'], 'idx_followups_user_status');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropIndex('idx_quotes_latest_status');
            $table->dropIndex('idx_quotes_latest_date');
            $table->dropIndex('idx_quotes_latest_accepted');
            $table->dropIndex('idx_quotes_creator_latest');
        });

        Schema::table('prospects', function (Blueprint $table) {
            $table->dropIndex('idx_prospects_stage_value');
            $table->dropIndex('idx_prospects_user_stage');
        });

        Schema::table('follow_ups', function (Blueprint $table) {
            $table->dropIndex('idx_followups_status_due');
            $table->dropIndex('idx_followups_user_status');
        });
    }
};
