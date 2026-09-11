<?php

namespace Tests\Feature;

use Carbon\CarbonImmutable;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\OfficialPublicDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicNoticesAndCatalogApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        CarbonImmutable::setTestNow('2026-09-11 12:00:00');
        $this->seed([DatabaseSeeder::class, OfficialPublicDataSeeder::class]);
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_public_notices_are_listed_with_current_count_and_details(): void
    {
        $response = $this->getJson('/api/v1/notices');

        $response->assertOk()
            ->assertJsonCount(4, 'data')
            ->assertJsonPath('meta.current_count', 2)
            ->assertJsonPath('data.0.data_nature', 'official_source');

        $id = collect($response->json('data'))->firstWhere('code', '07/2026')['id'];
        $this->getJson("/api/v1/notices/{$id}")
            ->assertOk()
            ->assertJsonPath('data.status', 'in_result')
            ->assertJsonCount(2, 'data.items');
    }

    public function test_notice_filters_accept_current_and_status(): void
    {
        $this->getJson('/api/v1/notices?current=1')
            ->assertOk()->assertJsonCount(2, 'data');
        $this->getJson('/api/v1/notices?status=closed')
            ->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_religious_queries_use_catalog_mode_without_scores(): void
    {
        foreach (['templo', 'igreja', 'assistência social'] as $term) {
            $response = $this->getJson('/api/v1/recommendations?query='.urlencode($term));

            $response->assertOk()
                ->assertJsonPath('recognized', true)
                ->assertJsonPath('mode', 'catalog')
                ->assertJsonCount(2, 'results')
                ->assertJsonPath('results.0.score', null)
                ->assertJsonCount(0, 'results.0.factors')
                ->assertJsonPath('results.0.offer_status', 'failed');
        }
    }

    public function test_ranked_searches_keep_their_original_mode(): void
    {
        $this->getJson('/api/v1/recommendations?query=coworking')
            ->assertOk()
            ->assertJsonPath('mode', 'ranked')
            ->assertJsonPath('results.0.score', 89);
    }
}
