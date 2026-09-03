<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BusinessCategory extends Model
{
    protected $fillable = ['slug', 'name', 'description', 'aliases', 'weights', 'active'];

    protected function casts(): array
    {
        return ['aliases' => 'array', 'weights' => 'array', 'active' => 'boolean'];
    }

    public function lotProfiles(): HasMany
    {
        return $this->hasMany(LotBusinessProfile::class);
    }

    public function rankingProfile(): HasOne
    {
        return $this->hasOne(RankingProfile::class);
    }
}
