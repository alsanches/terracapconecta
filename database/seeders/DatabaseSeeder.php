<?php

namespace Database\Seeders;

use App\Models\AdministrativeRegion;
use App\Models\BusinessCategory;
use App\Models\DataSource;
use App\Models\Lot;
use App\Models\LotBusinessProfile;
use App\Models\Notice;
use App\Models\NoticeItem;
use App\Models\RankingProfile;
use App\Models\RegionalIndicator;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $sources = $this->seedSources();
        $regions = $this->seedRegions($sources['regions']);
        $categories = $this->seedCategories();
        $this->seedRankingProfiles($categories);
        $notices = $this->seedNotices();
        $lots = $this->seedLots($regions);
        $this->seedNoticeItems($lots, $notices);
        $this->seedProfiles($lots, $categories);
        $this->seedIndicators($regions, $sources['indicators']);
        $this->seedAdmin();
    }

    private function seedSources(): array
    {
        $definitions = [
            'regions' => ['IPEDF — limites das RAs', 'ipedf-limites-ras', 'IPEDF', 'geojson_http', 'ipedf_regions_geojson', 'https://catalogo.ipe.df.gov.br/', 'anual', 'active', 'Camada oficial de limites administrativos.'],
            'indicators' => ['IPEDF — PDAD-A 2024', 'ipedf-pdad-a-2024', 'IPEDF', 'api_json', 'ipedf_pdad_demo', 'https://pdad.ipe.df.gov.br/', 'anual', 'active', 'Indicadores demonstrativos referenciados à PDAD-A.'],
            'lots' => ['Terracap — cadastro demonstrativo', 'terracap-cadastro-demo', 'Terracap', 'manual', 'terracap_manual_demo', null, 'manual', 'active', 'Cadastro inteiramente fictício usado no protótipo.'],
            'mobility' => ['Mobilidade GDF — futura integração', 'mobilidade-gdf-futura', 'GDF', 'api_json', 'mobility_gdf_future', null, 'mensal', 'paused', 'Integração simulada; sem dependência externa na apresentação.'],
        ];

        $sources = [];
        foreach ($definitions as $key => $data) {
            [$name, $slug, $organization, $sourceType, $adapterKey, $baseUrl, $frequency, $status, $notes] = $data;
            $sources[$key] = DataSource::query()->updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'organization' => $organization,
                'source_type' => $sourceType,
                'adapter_key' => $adapterKey,
                'base_url' => $baseUrl,
                'frequency' => $frequency,
                'status' => $status,
                'notes' => $notes,
            ]);
        }

        return $sources;
    }

    private function seedRegions(DataSource $source): array
    {
        $geojson = json_decode(file_get_contents(database_path('data/ras-df.geojson')), true, flags: JSON_THROW_ON_ERROR);
        throw_unless(count($geojson['features'] ?? []) === 35, 'O arquivo oficial deve conter exatamente 35 RAs.');

        $regions = [];
        foreach ($geojson['features'] as $feature) {
            $properties = $feature['properties'];
            $name = $this->displayName($properties['ra_nome']);
            [$longitude, $latitude] = $this->geometryCenter($feature['geometry']);
            $geometryJson = json_encode($feature['geometry'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $region = AdministrativeRegion::query()->updateOrCreate(
                ['official_code' => (string) $properties['ra_codigo']],
                [
                    'data_source_id' => $source->id,
                    'slug' => Str::slug($name),
                    'name' => $name,
                    'area_sq_km' => $properties['ra_areakm2'] ?? null,
                    'center_latitude' => $latitude,
                    'center_longitude' => $longitude,
                    'geometry_json' => $geometryJson,
                    'source_version' => 'Consulta WFS de 03/09/2026',
                    'source_url' => 'https://catalogo.ipe.df.gov.br/layers/geonode_data%3Ageonode%3Aregioes_administrativas/metadata_detail',
                ]
            );

            if (DB::getDriverName() === 'pgsql') {
                DB::statement(
                    'UPDATE administrative_regions SET geometry = ST_Multi(ST_SetSRID(ST_GeomFromGeoJSON(?), 4326)), display_geometry = ST_Multi(ST_SimplifyPreserveTopology(ST_SetSRID(ST_GeomFromGeoJSON(?), 4326), 0.00015)) WHERE id = ?',
                    [$geometryJson, $geometryJson, $region->id]
                );
            }

            $regions[$region->slug] = $region;
        }

        return $regions;
    }

    private function seedCategories(): array
    {
        $weights = ['target_audience' => 30, 'demand_density' => 25, 'income_fit' => 20, 'mobility_access' => 15, 'opportunity_gap' => 10];
        $definitions = [
            'bar-gastronomia' => ['Bar e gastronomia', 'Locais para alimentação, convivência e vida noturna.', ['bar', 'bares', 'restaurante', 'gastronomia', 'vida noturna']],
            'coworking' => ['Coworking', 'Escritórios compartilhados e serviços profissionais.', ['coworking', 'escritorio compartilhado', 'escritório compartilhado', 'servicos profissionais', 'serviços profissionais']],
            'comercio-servicos' => ['Comércio e serviços essenciais', 'Mercados, lojas e serviços de proximidade.', ['mercado', 'comercio de bairro', 'comércio de bairro', 'servicos essenciais', 'serviços essenciais']],
        ];

        $categories = [];
        foreach ($definitions as $slug => [$name, $description, $aliases]) {
            $categories[$slug] = BusinessCategory::query()->updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'description' => $description,
                'aliases' => $aliases,
                'weights' => $weights,
                'active' => true,
            ]);
        }

        return $categories;
    }

    private function seedNotices(): array
    {
        return [
            '2026-001' => Notice::query()->updateOrCreate(['code' => 'DEMO-2026/001'], [
                'title' => 'Edital demonstrativo de licitação pública',
                'modality' => 'Licitação pública',
                'opens_at' => '2026-09-01',
                'closes_at' => '2026-12-15',
                'status' => 'open',
                'description' => 'Documento fictício criado exclusivamente para demonstrar o Terracap Conecta.',
                'is_demo' => true,
            ]),
            '2026-002' => Notice::query()->updateOrCreate(['code' => 'DEMO-2026/002'], [
                'title' => 'Edital demonstrativo de imóveis comerciais',
                'modality' => 'Licitação pública',
                'opens_at' => '2026-10-01',
                'closes_at' => '2027-01-20',
                'status' => 'open',
                'description' => 'Valores, lotes e condições não correspondem a ofertas reais.',
                'is_demo' => true,
            ]),
        ];
    }

    private function seedRankingProfiles(array $categories): void
    {
        $weights = ['target_audience' => 30, 'demand_density' => 25, 'income_fit' => 20, 'mobility_access' => 15, 'opportunity_gap' => 10];

        foreach ($categories as $category) {
            RankingProfile::query()->updateOrCreate(['business_category_id' => $category->id], [
                'name' => 'Perfil demonstrativo — '.$category->name,
                'weights' => $weights,
                'methodology_note' => 'Nota reproduzível de 0 a 100; cada fator recebe nota de 0 a 100 e é multiplicado pelo peso configurado.',
                'active' => true,
            ]);
        }
    }

    private function seedLots(array $regions): array
    {
        $definitions = [
            ['TC-D01', 'Polo gastronômico de Taguatinga', 'Setor C Norte, lote demonstrativo 01', 820, 'Uso comercial', 'Comércio de bens e prestação de serviços', -15.8327, -48.0563, 'taguatinga', true, true],
            ['TC-D02', 'Centro empresarial de Águas Claras', 'Avenida das Araucárias, lote demonstrativo 02', 610, 'Uso misto', 'Serviços profissionais e escritórios', -15.8398, -48.0254, 'aguas-claras', true, true],
            ['TC-D03', 'Comércio de proximidade em Planaltina', 'Avenida Independência, lote demonstrativo 03', 950, 'Uso comercial', 'Comércio varejista e serviços essenciais', -15.6218, -47.6515, 'planaltina', true, true],
            ['TC-D04', 'Área comercial central', 'Asa Norte, lote demonstrativo 04', 540, 'Uso comercial', 'Comércio e serviços', -15.7710, -47.8823, 'plano-piloto', false, false],
            ['TC-D05', 'Eixo de serviços do Guará', 'QE 20, lote demonstrativo 05', 720, 'Uso comercial', 'Comércio e serviços', -15.8265, -47.9820, 'guara', false, false],
            ['TC-D06', 'Centro comercial de Ceilândia', 'QNM, lote demonstrativo 06', 880, 'Uso comercial', 'Comércio varejista', -15.8152, -48.1051, 'ceilandia', false, false],
            ['TC-D07', 'Unidade comercial de Samambaia', 'QS 406, lote demonstrativo 07', 690, 'Uso comercial', 'Comércio de bairro', -15.8721, -48.0912, 'samambaia', false, false],
            ['TC-D08', 'Área de serviços do Gama', 'Setor Central, lote demonstrativo 08', 1040, 'Uso comercial', 'Serviços e abastecimento', -16.0181, -48.0622, 'gama', false, false],
            ['TC-D09', 'Núcleo empresarial de Sobradinho', 'Quadra 08, lote demonstrativo 09', 760, 'Uso comercial', 'Serviços profissionais', -15.6505, -47.7910, 'sobradinho', false, false],
            ['TC-D10', 'Comércio local de Santa Maria', 'CL 115, lote demonstrativo 10', 830, 'Uso comercial', 'Comércio e serviços essenciais', -16.0018, -47.9870, 'santa-maria', false, false],
        ];

        $lots = [];
        foreach ($definitions as $data) {
            [$code, $title, $address, $area, $zoning, $destination, $latitude, $longitude, $regionSlug, $featured, $searchEnabled] = $data;
            throw_unless(isset($regions[$regionSlug]), "RA não encontrada para {$regionSlug}.");
            $lot = Lot::query()->updateOrCreate(['code' => $code], [
                'administrative_region_id' => $regions[$regionSlug]->id,
                'title' => $title,
                'address' => $address,
                'area_sqm' => $area,
                'zoning' => $zoning,
                'destination' => $destination,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'status' => 'published',
                'is_demo' => true,
                'is_featured' => $featured,
                'search_enabled' => $searchEnabled,
                'published_at' => now(),
            ]);

            if (DB::getDriverName() === 'pgsql') {
                DB::statement('UPDATE lots SET location = ST_SetSRID(ST_MakePoint(?, ?), 4326) WHERE id = ?', [$longitude, $latitude, $lot->id]);
            }
            $lots[$code] = $lot;
        }

        return $lots;
    }

    private function seedNoticeItems(array $lots, array $notices): void
    {
        foreach (array_values($lots) as $index => $lot) {
            $notice = $index < 5 ? $notices['2026-001'] : $notices['2026-002'];
            NoticeItem::query()->updateOrCreate(
                ['notice_id' => $notice->id, 'item_number' => str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT)],
                [
                    'lot_id' => $lot->id,
                    'minimum_price' => 420000 + ($index * 73500),
                    'payment_terms' => 'Condições demonstrativas: sinal de 10% e saldo conforme edital fictício.',
                    'status' => 'open',
                ]
            );
        }
    }

    private function seedProfiles(array $lots, array $categories): void
    {
        $profiles = [
            ['TC-D01', 'bar-gastronomia', [92, 88, 78, 86, 82], ['Concentração demonstrativa de adultos economicamente ativos.', 'Eixo consolidado de alimentação e convivência.', 'Boa conexão por transporte coletivo.']],
            ['TC-D02', 'coworking', [90, 91, 89, 94, 75], ['Alta presença demonstrativa de profissionais e empresas.', 'Renda compatível com serviços compartilhados.', 'Acesso por metrô e vias estruturantes.']],
            ['TC-D03', 'comercio-servicos', [86, 93, 82, 76, 95], ['Área residencial com demanda demonstrativa de proximidade.', 'Oportunidade simulada de menor oferta relativa.', 'Lote compatível com comércio varejista.']],
        ];

        foreach ($profiles as [$lotCode, $categorySlug, $scores, $reasons]) {
            LotBusinessProfile::query()->updateOrCreate(
                ['lot_id' => $lots[$lotCode]->id, 'business_category_id' => $categories[$categorySlug]->id],
                [
                    'target_audience_score' => $scores[0],
                    'demand_density_score' => $scores[1],
                    'income_fit_score' => $scores[2],
                    'mobility_access_score' => $scores[3],
                    'opportunity_gap_score' => $scores[4],
                    'reasons' => $reasons,
                ]
            );
        }
    }

    private function seedIndicators(array $regions, DataSource $source): void
    {
        foreach (['taguatinga' => [227000, 4800], 'aguas-claras' => [161000, 7200], 'planaltina' => [190000, 3100]] as $slug => [$population, $income]) {
            foreach ([['population', 'População estimada', $population, 'habitantes'], ['income_per_capita', 'Renda per capita demonstrativa', $income, 'R$/mês']] as [$key, $label, $value, $unit]) {
                RegionalIndicator::query()->updateOrCreate(
                    ['administrative_region_id' => $regions[$slug]->id, 'key' => $key, 'reference_year' => 2024],
                    ['data_source_id' => $source->id, 'label' => $label, 'value' => $value, 'unit' => $unit, 'is_demo' => true]
                );
            }
        }
    }

    private function seedAdmin(): void
    {
        if (! $email = env('ADMIN_EMAIL')) {
            return;
        }

        throw_unless(env('ADMIN_PASSWORD'), 'ADMIN_PASSWORD deve ser definido quando ADMIN_EMAIL estiver configurado.');
        User::query()->updateOrCreate(['email' => $email], [
            'name' => env('ADMIN_NAME', 'Administrador Terracap Conecta'),
            'password' => env('ADMIN_PASSWORD'),
            'role' => 'admin',
            'active' => true,
        ]);
    }

    private function geometryCenter(array $geometry): array
    {
        $coordinates = [];
        $collect = function (array $node) use (&$collect, &$coordinates): void {
            if (isset($node[0], $node[1]) && is_numeric($node[0]) && is_numeric($node[1])) {
                $coordinates[] = [(float) $node[0], (float) $node[1]];

                return;
            }
            foreach ($node as $child) {
                if (is_array($child)) {
                    $collect($child);
                }
            }
        };
        $collect($geometry['coordinates']);
        $longitudes = array_column($coordinates, 0);
        $latitudes = array_column($coordinates, 1);

        return [(min($longitudes) + max($longitudes)) / 2, (min($latitudes) + max($latitudes)) / 2];
    }

    private function displayName(string $officialName): string
    {
        return match ($officialName) {
            'AGUA QUENTE' => 'Água Quente',
            'SOL NASCENTE E POR DO SOL' => 'Sol Nascente e Pôr do Sol',
            'SUDOESTE/OCTOGONAL' => 'Sudoeste/Octogonal',
            'SCIA' => 'SCIA',
            'SIA' => 'SIA',
            default => Str::title(Str::lower($officialName)),
        };
    }
}
