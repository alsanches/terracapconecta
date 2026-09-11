<?php

namespace Tests\Feature;

use App\Models\BusinessCategory;
use App\Models\Lot;
use App\Models\Notice;
use App\Services\RegionLocator;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\OfficialPublicDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficialPublicDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_official_seed_is_idempotent_and_keeps_demo_data_separate(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(OfficialPublicDataSeeder::class);
        $this->seed(OfficialPublicDataSeeder::class);

        $this->assertSame(4, Notice::query()->where('is_demo', false)->count());
        $this->assertSame(2, Lot::query()->where('is_demo', false)->count());
        $this->assertSame(10, Lot::query()->where('is_demo', true)->count());

        $category = BusinessCategory::query()->where('slug', 'templos-assistencia-social')->firstOrFail();
        $this->assertSame('catalog', $category->result_mode);

        $item18 = Lot::query()->where('code', '819340-1')->with('noticeItems')->firstOrFail();
        $this->assertFalse($item18->is_demo);
        $this->assertSame('failed', $item18->offer_status);
        $this->assertSame('1456.50', $item18->noticeItems->first()->minimum_price);
        $this->assertSame('4369.50', $item18->noticeItems->first()->deposit_amount);
    }

    public function test_approximate_coordinates_remain_inside_the_declared_regions(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(OfficialPublicDataSeeder::class);

        $locator = app(RegionLocator::class);

        foreach (Lot::query()->where('is_demo', false)->with('region')->get() as $lot) {
            $this->assertSame('approximate', $lot->location_precision);
            $this->assertTrue($locator->locate($lot->latitude, $lot->longitude)?->is($lot->region));
        }
    }
}
