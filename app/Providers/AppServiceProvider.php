<?php

namespace App\Providers;

use App\Models\AuditLog;
use App\Models\DataSource;
use App\Models\Lot;
use App\Models\Notice;
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(DiagnosingHealth::class, fn () => DB::select('select 1'));

        foreach ([Lot::class, Notice::class, DataSource::class] as $modelClass) {
            $modelClass::created(fn ($model) => $this->audit($model, 'created'));
            $modelClass::updated(fn ($model) => $this->audit($model, 'updated'));
            $modelClass::deleted(fn ($model) => $this->audit($model, 'deleted'));
        }

        Lot::saved(function (Lot $lot): void {
            if (DB::getDriverName() === 'pgsql') {
                DB::statement('UPDATE lots SET location = ST_SetSRID(ST_MakePoint(?, ?), 4326) WHERE id = ?', [$lot->longitude, $lot->latitude, $lot->id]);
            }
        });
    }

    private function audit($model, string $action): void
    {
        if (! Schema::hasTable('audit_logs')) {
            return;
        }

        AuditLog::query()->create([
            'user_id' => auth()->id(),
            'auditable_type' => $model::class,
            'auditable_id' => $model->getKey(),
            'action' => $action,
            'before' => $action === 'updated' ? $model->getOriginal() : null,
            'after' => $action === 'deleted' ? null : $model->attributesToArray(),
            'ip_address' => request()?->ip(),
            'created_at' => now(),
        ]);
    }
}
