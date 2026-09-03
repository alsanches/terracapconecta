<?php

namespace App\Services;

use App\Models\AdministrativeRegion;
use Illuminate\Support\Facades\DB;

class RegionLocator
{
    public function locate(float $latitude, float $longitude): ?AdministrativeRegion
    {
        if (DB::getDriverName() === 'pgsql') {
            return AdministrativeRegion::query()
                ->whereRaw(
                    'ST_Covers(geometry, ST_SetSRID(ST_MakePoint(?, ?), 4326))',
                    [$longitude, $latitude]
                )
                ->first();
        }

        return AdministrativeRegion::query()->get()->first(
            fn (AdministrativeRegion $region) => $this->geometryContains(
                json_decode($region->geometry_json, true),
                $longitude,
                $latitude
            )
        );
    }

    private function geometryContains(?array $geometry, float $x, float $y): bool
    {
        if (($geometry['type'] ?? null) !== 'MultiPolygon') {
            return false;
        }

        foreach ($geometry['coordinates'] as $polygon) {
            if ($this->ringContains($polygon[0] ?? [], $x, $y)) {
                $insideHole = false;
                foreach (array_slice($polygon, 1) as $hole) {
                    $insideHole = $insideHole || $this->ringContains($hole, $x, $y);
                }

                if (! $insideHole) {
                    return true;
                }
            }
        }

        return false;
    }

    private function ringContains(array $ring, float $x, float $y): bool
    {
        $inside = false;
        $count = count($ring);

        for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
            [$xi, $yi] = $ring[$i];
            [$xj, $yj] = $ring[$j];
            $intersects = (($yi > $y) !== ($yj > $y))
                && ($x < (($xj - $xi) * ($y - $yi) / (($yj - $yi) ?: PHP_FLOAT_EPSILON)) + $xi);

            if ($intersects) {
                $inside = ! $inside;
            }
        }

        return $inside;
    }
}
