<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notices', function (Blueprint $table): void {
            $table->boolean('public_visible')->default(false)->after('status');
            $table->string('procedure_type')->nullable()->after('modality');
            $table->string('process_number')->nullable()->after('procedure_type');
            $table->date('published_on')->nullable()->after('closes_at');
            $table->date('deposit_deadline')->nullable()->after('published_on');
            $table->date('proposal_deadline')->nullable()->after('deposit_deadline');
            $table->dateTime('auction_at')->nullable()->after('proposal_deadline');
            $table->text('regions_summary')->nullable()->after('description');
            $table->string('official_page_url', 2048)->nullable()->after('document_url');
            $table->string('proposal_url', 2048)->nullable()->after('official_page_url');
            $table->string('result_url', 2048)->nullable()->after('proposal_url');
            $table->dateTime('source_checked_at')->nullable()->after('result_url');
        });

        Schema::table('notice_items', function (Blueprint $table): void {
            $table->string('amount_type')->default('sale_price')->after('item_number');
            $table->decimal('appraisal_value', 14, 2)->nullable()->after('minimum_price');
            $table->decimal('deposit_amount', 14, 2)->nullable()->after('appraisal_value');
            $table->text('outcome_notes')->nullable()->after('payment_terms');
        });

        Schema::table('lots', function (Blueprint $table): void {
            $table->string('location_precision')->default('approximate')->after('longitude');
            $table->string('offer_status')->default('available')->after('status');
            $table->string('source_url', 2048)->nullable()->after('offer_status');
            $table->dateTime('source_checked_at')->nullable()->after('source_url');
        });

        Schema::table('business_categories', function (Blueprint $table): void {
            $table->string('result_mode')->default('ranked')->after('description');
        });

        Schema::table('lot_business_profiles', function (Blueprint $table): void {
            $table->unsignedTinyInteger('target_audience_score')->nullable()->change();
            $table->unsignedTinyInteger('demand_density_score')->nullable()->change();
            $table->unsignedTinyInteger('income_fit_score')->nullable()->change();
            $table->unsignedTinyInteger('mobility_access_score')->nullable()->change();
            $table->unsignedTinyInteger('opportunity_gap_score')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('lot_business_profiles', function (Blueprint $table): void {
            $table->unsignedTinyInteger('target_audience_score')->nullable(false)->change();
            $table->unsignedTinyInteger('demand_density_score')->nullable(false)->change();
            $table->unsignedTinyInteger('income_fit_score')->nullable(false)->change();
            $table->unsignedTinyInteger('mobility_access_score')->nullable(false)->change();
            $table->unsignedTinyInteger('opportunity_gap_score')->nullable(false)->change();
        });

        Schema::table('business_categories', function (Blueprint $table): void {
            $table->dropColumn('result_mode');
        });

        Schema::table('lots', function (Blueprint $table): void {
            $table->dropColumn(['location_precision', 'offer_status', 'source_url', 'source_checked_at']);
        });

        Schema::table('notice_items', function (Blueprint $table): void {
            $table->dropColumn(['amount_type', 'appraisal_value', 'deposit_amount', 'outcome_notes']);
        });

        Schema::table('notices', function (Blueprint $table): void {
            $table->dropColumn([
                'public_visible', 'procedure_type', 'process_number', 'published_on',
                'deposit_deadline', 'proposal_deadline', 'auction_at', 'regions_summary',
                'official_page_url', 'proposal_url', 'result_url', 'source_checked_at',
            ]);
        });
    }
};
