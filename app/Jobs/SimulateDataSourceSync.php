<?php

namespace App\Jobs;

use App\Models\DataSource;
use App\Models\SyncRun;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class SimulateDataSourceSync implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $sourceId, public ?int $userId = null) {}

    public function handle(): void
    {
        $source = DataSource::query()->findOrFail($this->sourceId);
        $run = SyncRun::query()->create([
            'data_source_id' => $source->id,
            'user_id' => $this->userId,
            'mode' => 'simulation',
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            throw_unless($source->status === 'active', 'A fonte precisa estar ativa para executar uma sincronização.');

            $received = match ($source->adapter_key) {
                'ipedf_regions_geojson' => 35,
                'ipedf_pdad_demo' => 3,
                'terracap_manual_demo' => 10,
                'mobility_gdf_future' => 24,
                default => throw new \UnexpectedValueException('Adaptador não autorizado ou arquivo de simulação inválido.'),
            };

            $run->update([
                'status' => 'success',
                'received_count' => $received,
                'imported_count' => $received,
                'metadata' => ['fixture' => true, 'adapter' => $source->adapter_key],
                'finished_at' => now(),
            ]);
            $source->update(['last_synced_at' => now()]);
        } catch (Throwable $exception) {
            $run->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'finished_at' => now(),
            ]);

            throw $exception;
        }
    }
}
