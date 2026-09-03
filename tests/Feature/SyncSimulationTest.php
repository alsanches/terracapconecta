<?php

namespace Tests\Feature;

use App\Jobs\SimulateDataSourceSync;
use App\Models\DataSource;
use App\Models\SyncRun;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncSimulationTest extends TestCase
{
    use RefreshDatabase;

    public function test_simulation_records_success_and_processed_counts(): void
    {
        $this->seed();
        $source = DataSource::query()->where('adapter_key', 'ipedf_regions_geojson')->firstOrFail();

        (new SimulateDataSourceSync($source->id))->handle();

        $run = SyncRun::query()->latest('id')->firstOrFail();
        $this->assertSame('success', $run->status);
        $this->assertSame(35, $run->received_count);
        $this->assertSame(35, $run->imported_count);
        $this->assertNotNull($source->fresh()->last_synced_at);
    }

    public function test_paused_source_records_a_failed_simulation(): void
    {
        $this->seed();
        $source = DataSource::query()->where('status', 'paused')->firstOrFail();

        try {
            (new SimulateDataSourceSync($source->id))->handle();
            $this->fail('A fonte pausada deveria falhar.');
        } catch (\RuntimeException) {
            $run = SyncRun::query()->latest('id')->firstOrFail();
            $this->assertSame('failed', $run->status);
            $this->assertStringContainsString('precisa estar ativa', $run->error_message);
        }
    }

    public function test_unauthorized_adapter_is_rejected_and_recorded(): void
    {
        $this->seed();
        $source = DataSource::query()->create([
            'name' => 'Arquivo inválido', 'slug' => 'arquivo-invalido', 'organization' => 'Demonstração',
            'source_type' => 'csv', 'adapter_key' => 'invalid_fixture', 'frequency' => 'manual', 'status' => 'active',
        ]);

        try {
            (new SimulateDataSourceSync($source->id))->handle();
            $this->fail('O adaptador não autorizado deveria falhar.');
        } catch (\UnexpectedValueException) {
            $run = SyncRun::query()->latest('id')->firstOrFail();
            $this->assertSame('failed', $run->status);
            $this->assertStringContainsString('não autorizado', $run->error_message);
        }
    }
}
