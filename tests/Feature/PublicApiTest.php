<?php

namespace Tests\Feature;

use App\Models\Lot;
use App\Services\RegionLocator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_home_and_health_are_available(): void
    {
        $this->get('/')->assertOk()->assertSee('O lugar certo para');
        $this->get('/up')->assertOk();
    }

    public function test_regions_endpoint_returns_exactly_35_valid_features(): void
    {
        $response = $this->getJson('/api/v1/regions')->assertOk()
            ->assertJsonPath('type', 'FeatureCollection')
            ->assertJsonPath('meta.count', 35);

        $this->assertCount(35, $response->json('features'));
        $response->assertJsonPath('features.0.type', 'Feature');
        $this->assertContains($response->json('features.0.geometry.type'), ['Polygon', 'MultiPolygon']);
    }

    public function test_lots_endpoint_returns_ten_published_demo_lots(): void
    {
        $response = $this->getJson('/api/v1/lots')->assertOk()->assertJsonPath('meta.count', 10);
        $this->assertCount(10, $response->json('data'));
        $this->assertSame(3, collect($response->json('data'))->where('search_enabled', true)->count());
    }

    public function test_the_three_supported_searches_are_deterministic(): void
    {
        foreach ([
            'quero abrir um bar' => ['bar-gastronomia', 'Taguatinga', 86],
            'escritório compartilhado' => ['coworking', 'Águas Claras', 89],
            'mercado de bairro' => ['comercio-servicos', 'Planaltina', 86],
        ] as $query => [$slug, $region, $score]) {
            $response = $this->getJson('/api/v1/recommendations?query='.urlencode($query))->assertOk()
                ->assertJsonPath('recognized', true)
                ->assertJsonPath('category.slug', $slug)
                ->assertJsonPath('results.0.region', $region)
                ->assertJsonPath('results.0.score', $score);

            $this->assertCount(5, $response->json('results.0.factors'));
        }
    }

    public function test_unknown_search_does_not_invent_a_recommendation(): void
    {
        $this->getJson('/api/v1/recommendations?query=hotel')
            ->assertOk()
            ->assertJsonPath('recognized', false)
            ->assertJsonCount(0, 'results')
            ->assertJsonCount(3, 'suggestions');
    }

    public function test_every_demo_lot_is_inside_its_registered_region(): void
    {
        $locator = app(RegionLocator::class);

        Lot::query()->with('region')->each(function (Lot $lot) use ($locator): void {
            $this->assertSame($lot->region->id, $locator->locate($lot->latitude, $lot->longitude)?->id, $lot->code);
        });
    }

    public function test_draft_lot_is_not_public(): void
    {
        $lot = Lot::query()->firstOrFail();
        $lot->update(['status' => 'draft', 'published_at' => null]);

        $this->getJson('/api/v1/lots/'.$lot->id)->assertNotFound();
    }
}
