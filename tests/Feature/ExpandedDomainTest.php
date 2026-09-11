<?php

namespace Tests\Feature;

use App\Models\BusinessCategory;
use App\Models\Notice;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ExpandedDomainTest extends TestCase
{
    use RefreshDatabase;

    public function test_incremental_schema_supports_public_notices_and_catalog_lots(): void
    {
        $this->assertTrue(Schema::hasColumns('notices', [
            'public_visible', 'procedure_type', 'process_number', 'published_on',
            'deposit_deadline', 'proposal_deadline', 'auction_at', 'regions_summary',
            'official_page_url', 'proposal_url', 'result_url', 'source_checked_at',
        ]));
        $this->assertTrue(Schema::hasColumns('notice_items', [
            'amount_type', 'appraisal_value', 'deposit_amount', 'outcome_notes',
        ]));
        $this->assertTrue(Schema::hasColumns('lots', [
            'location_precision', 'offer_status', 'source_url', 'source_checked_at',
        ]));
        $this->assertTrue(Schema::hasColumn('business_categories', 'result_mode'));
    }

    public function test_notice_vigency_uses_the_deadline_and_publication_state(): void
    {
        CarbonImmutable::setTestNow('2026-09-11 12:00:00');

        $notice = new Notice([
            'status' => 'open',
            'public_visible' => true,
            'proposal_deadline' => '2026-09-11',
        ]);

        $this->assertTrue($notice->isCurrentlyOpen());

        $notice->proposal_deadline = '2026-09-10';
        $this->assertFalse($notice->isCurrentlyOpen());
        $this->assertSame('closed', $notice->publicStatus());

        $category = BusinessCategory::query()->create([
            'slug' => 'catalogo', 'name' => 'Catálogo', 'aliases' => ['catalogo'],
            'weights' => [], 'result_mode' => 'catalog', 'active' => true,
        ]);
        $this->assertSame('catalog', $category->result_mode);

        CarbonImmutable::setTestNow();
    }
}
