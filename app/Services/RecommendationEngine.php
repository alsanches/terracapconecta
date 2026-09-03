<?php

namespace App\Services;

use App\Models\BusinessCategory;
use App\Models\LotBusinessProfile;
use Illuminate\Support\Str;

class RecommendationEngine
{
    private const FACTORS = [
        'target_audience_score' => ['label' => 'Incidência do público-alvo', 'weight_key' => 'target_audience'],
        'demand_density_score' => ['label' => 'Demanda e densidade', 'weight_key' => 'demand_density'],
        'income_fit_score' => ['label' => 'Compatibilidade de renda', 'weight_key' => 'income_fit'],
        'mobility_access_score' => ['label' => 'Mobilidade e acesso', 'weight_key' => 'mobility_access'],
        'opportunity_gap_score' => ['label' => 'Carência ou oportunidade', 'weight_key' => 'opportunity_gap'],
    ];

    public function recommend(string $query): array
    {
        $category = $this->recognize($query);
        $suggestions = BusinessCategory::query()->where('active', true)->orderBy('id')->pluck('name')->all();

        if (! $category) {
            return [
                'recognized' => false,
                'message' => 'Ainda não reconhecemos esse tipo de negócio no protótipo.',
                'suggestions' => $suggestions,
                'results' => [],
            ];
        }

        $rankingProfile = $category->rankingProfile;
        $weights = $rankingProfile?->weights ?? $category->weights;

        $profiles = LotBusinessProfile::query()
            ->where('business_category_id', $category->id)
            ->whereHas('lot', fn ($query) => $query->published()->where('search_enabled', true))
            ->with(['lot.region', 'lot.noticeItems.notice'])
            ->get();

        $results = $profiles->map(function (LotBusinessProfile $profile) use ($weights) {
            $lot = $profile->lot;
            $noticeItem = $lot->noticeItems->firstWhere('status', 'open') ?? $lot->noticeItems->first();
            $factors = collect(self::FACTORS)->map(function (array $definition, string $field) use ($profile, $weights) {
                $score = (int) $profile->{$field};
                $weight = (int) ($weights[$definition['weight_key']] ?? 0);

                return [
                    'key' => $field,
                    'label' => $definition['label'],
                    'weight' => $weight,
                    'score' => $score,
                    'contribution' => round($score * ($weight / 100), 1),
                ];
            })->values();

            return [
                'lot_id' => $lot->id,
                'code' => $lot->code,
                'title' => $lot->title,
                'region' => $lot->region->name,
                'coordinates' => [(float) $lot->longitude, (float) $lot->latitude],
                'score' => (int) round($factors->sum('contribution')),
                'factors' => $factors->all(),
                'reasons' => $profile->reasons,
                'notice' => $noticeItem ? [
                    'code' => $noticeItem->notice->code,
                    'item' => $noticeItem->item_number,
                    'minimum_price' => $noticeItem->minimum_price ? (float) $noticeItem->minimum_price : null,
                ] : null,
                'is_demo' => $lot->is_demo,
            ];
        })->sortByDesc('score')->values()->all();

        return [
            'recognized' => true,
            'category' => ['slug' => $category->slug, 'name' => $category->name],
            'message' => count($results).' oportunidade(s) demonstrativa(s) encontrada(s).',
            'suggestions' => $suggestions,
            'results' => $results,
            'methodology' => $rankingProfile?->methodology_note ?? 'Pontuação demonstrativa e explicável, calculada por cinco fatores ponderados.',
        ];
    }

    private function recognize(string $query): ?BusinessCategory
    {
        $normalized = Str::lower(Str::ascii(trim($query)));

        return BusinessCategory::query()->where('active', true)->get()->first(function (BusinessCategory $category) use ($normalized) {
            return collect($category->aliases)->contains(
                fn (string $alias) => str_contains($normalized, Str::lower(Str::ascii($alias)))
            );
        });
    }
}
