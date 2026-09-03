<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('organization');
            $table->string('source_type');
            $table->string('adapter_key');
            $table->string('base_url')->nullable();
            $table->string('frequency')->default('manual');
            $table->string('status')->default('draft');
            $table->string('credential_reference')->nullable();
            $table->string('contact')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('administrative_regions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_source_id')->nullable()->constrained()->nullOnDelete();
            $table->string('official_code')->unique();
            $table->string('slug')->unique();
            $table->string('name');
            $table->decimal('area_sq_km', 12, 4)->nullable();
            $table->decimal('center_latitude', 10, 7)->nullable();
            $table->decimal('center_longitude', 10, 7)->nullable();
            $table->longText('geometry_json');
            $table->string('source_version')->nullable();
            $table->string('source_url')->nullable();
            $table->timestamps();
        });

        Schema::create('business_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('aliases');
            $table->json('weights');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('ranking_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_category_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->json('weights');
            $table->text('methodology_note');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('title');
            $table->string('modality');
            $table->date('opens_at')->nullable();
            $table->date('closes_at')->nullable();
            $table->string('status')->default('draft');
            $table->text('description')->nullable();
            $table->string('document_path')->nullable();
            $table->string('document_url')->nullable();
            $table->boolean('is_demo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('lots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('administrative_region_id')->constrained()->restrictOnDelete();
            $table->string('code')->unique();
            $table->string('title');
            $table->string('address');
            $table->decimal('area_sqm', 12, 2);
            $table->string('zoning');
            $table->string('destination');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->longText('boundary_json')->nullable();
            $table->string('status')->default('draft');
            $table->boolean('is_demo')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('search_enabled')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'administrative_region_id']);
        });

        Schema::create('notice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lot_id')->constrained()->cascadeOnDelete();
            $table->string('item_number');
            $table->decimal('minimum_price', 14, 2)->nullable();
            $table->text('payment_terms')->nullable();
            $table->string('status')->default('open');
            $table->timestamps();
            $table->unique(['notice_id', 'item_number']);
        });

        Schema::create('lot_business_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lot_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_category_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('target_audience_score');
            $table->unsignedTinyInteger('demand_density_score');
            $table->unsignedTinyInteger('income_fit_score');
            $table->unsignedTinyInteger('mobility_access_score');
            $table->unsignedTinyInteger('opportunity_gap_score');
            $table->json('reasons');
            $table->timestamps();
            $table->unique(['lot_id', 'business_category_id']);
        });

        Schema::create('regional_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('administrative_region_id')->constrained()->cascadeOnDelete();
            $table->foreignId('data_source_id')->nullable()->constrained()->nullOnDelete();
            $table->string('key');
            $table->string('label');
            $table->decimal('value', 16, 4);
            $table->string('unit');
            $table->unsignedSmallInteger('reference_year');
            $table->boolean('is_demo')->default(false);
            $table->timestamps();
            $table->unique(['administrative_region_id', 'key', 'reference_year']);
        });

        Schema::create('sync_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_source_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('mode')->default('simulation');
            $table->string('status')->default('queued');
            $table->unsignedInteger('received_count')->default(0);
            $table->unsignedInteger('imported_count')->default(0);
            $table->unsignedInteger('rejected_count')->default(0);
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('auditable_type');
            $table->unsignedBigInteger('auditable_id');
            $table->string('action');
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['auditable_type', 'auditable_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('admin');
            $table->boolean('active')->default(true);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE EXTENSION IF NOT EXISTS postgis');
            DB::statement('ALTER TABLE administrative_regions ADD COLUMN geometry geometry(MultiPolygon, 4326)');
            DB::statement('ALTER TABLE administrative_regions ADD COLUMN display_geometry geometry(MultiPolygon, 4326)');
            DB::statement('ALTER TABLE lots ADD COLUMN location geometry(Point, 4326)');
            DB::statement('ALTER TABLE lots ADD COLUMN boundary geometry(MultiPolygon, 4326)');
            DB::statement('CREATE INDEX administrative_regions_geometry_gist ON administrative_regions USING GIST (geometry)');
            DB::statement('CREATE INDEX administrative_regions_display_geometry_gist ON administrative_regions USING GIST (display_geometry)');
            DB::statement('CREATE INDEX lots_location_gist ON lots USING GIST (location)');
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'active']);
        });

        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('sync_runs');
        Schema::dropIfExists('regional_indicators');
        Schema::dropIfExists('lot_business_profiles');
        Schema::dropIfExists('notice_items');
        Schema::dropIfExists('lots');
        Schema::dropIfExists('notices');
        Schema::dropIfExists('ranking_profiles');
        Schema::dropIfExists('business_categories');
        Schema::dropIfExists('administrative_regions');
        Schema::dropIfExists('data_sources');
    }
};
