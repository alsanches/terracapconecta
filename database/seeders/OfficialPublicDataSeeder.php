<?php

namespace Database\Seeders;

use App\Models\AdministrativeRegion;
use App\Models\BusinessCategory;
use App\Models\Lot;
use App\Models\LotBusinessProfile;
use App\Models\Notice;
use App\Models\NoticeItem;
use App\Services\RegionLocator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OfficialPublicDataSeeder extends Seeder
{
    private const CHECKED_AT = '2026-09-11 12:00:00';

    private const CHURCH_NOTICE_URL = 'https://www.terracap.df.gov.br/index.php/compre-imoveis/licitacoes/listagem-compre-imoveis-licitacao/337-edital-de-licitacao-07-2026-programa-igreja-legal';

    public function run(): void
    {
        DB::transaction(function (): void {
            $notices = $this->seedNotices();
            $category = $this->seedCategory();

            $this->seedHistoricalLots($notices['church'], $category);
        });
    }

    private function seedNotices(): array
    {
        $definitions = [
            'develops' => [
                'code' => '12/2026',
                'title' => 'Edital de Licitação 12/2026 — Concessão Desenvolve/DF',
                'modality' => 'Licitação pública eletrônica',
                'procedure_type' => 'Concessão de direito real de uso — CDRU',
                'process_number' => null,
                'opens_at' => '2026-09-01',
                'closes_at' => '2026-09-23',
                'published_on' => '2026-09-01',
                'deposit_deadline' => '2026-09-22',
                'proposal_deadline' => '2026-09-23',
                'auction_at' => '2026-09-23 10:00:00',
                'status' => 'open',
                'description' => 'Concessão de imóveis no âmbito do Programa Desenvolve/DF. Consulte o edital para imóveis, condições e horários oficiais.',
                'regions_summary' => 'Ceilândia, Gama, Recanto das Emas, Samambaia, Sobradinho e outras regiões',
                'document_url' => 'https://www.terracap.df.gov.br/uploads/edicts/6aa1a70f0bf56.pdf',
                'official_page_url' => 'https://www.terracap.df.gov.br/index.php/compre-imoveis/licitacoes/listagem-compre-imoveis-licitacao/343-edital-de-licitacao-n-12-2026-concessao-desenvolve-df',
                'proposal_url' => null,
                'result_url' => null,
            ],
            'agro' => [
                'code' => 'CHAMAMENTO-01/2026',
                'title' => 'Chamamento Público 01/2026 — Polo Agroindustrial do Rio Preto',
                'modality' => 'Chamamento público',
                'procedure_type' => 'Seleção de interessados',
                'process_number' => null,
                'opens_at' => '2026-07-01',
                'closes_at' => '2026-10-16',
                'published_on' => '2026-07-01',
                'deposit_deadline' => null,
                'proposal_deadline' => '2026-10-16',
                'auction_at' => null,
                'status' => 'open',
                'description' => 'Seleção de interessados para implantação do Polo Agroindustrial do Rio Preto, em Planaltina, com prazo prorrogado para propostas.',
                'regions_summary' => 'Planaltina — Polo Agroindustrial do Rio Preto',
                'document_url' => null,
                'official_page_url' => 'https://www.terracap.df.gov.br/index.php/noticias/1507-prazo-para-apresentacao-de-propostas-para-o-polo-agroindustrial-do-rio-preto-e-prorrogado-ate-16-de-outubro',
                'proposal_url' => null,
                'result_url' => null,
            ],
            'sale' => [
                'code' => '11/2026',
                'title' => 'Edital de Licitação 11/2026 — Venda de Imóveis',
                'modality' => 'Licitação pública eletrônica',
                'procedure_type' => 'Venda de imóveis',
                'process_number' => null,
                'opens_at' => '2026-08-01',
                'closes_at' => '2026-09-11',
                'published_on' => '2026-08-01',
                'deposit_deadline' => '2026-09-10',
                'proposal_deadline' => '2026-09-11',
                'auction_at' => '2026-09-11 10:00:00',
                'status' => 'closed',
                'description' => 'Venda pública de imóveis da Terracap. Registro mantido como histórico após o encerramento do recebimento de propostas.',
                'regions_summary' => 'Diversas regiões administrativas do Distrito Federal',
                'document_url' => 'https://www.terracap.df.gov.br/uploads/edicts/6aa1abcf0c3d3.pdf',
                'official_page_url' => 'https://www.terracap.df.gov.br/index.php/compre-imoveis/licitacoes/listagem-compre-imoveis-licitacao/342-edital-de-licitacao-11-2026-venda-de-imoveis',
                'proposal_url' => null,
                'result_url' => null,
            ],
            'church' => [
                'code' => '07/2026',
                'title' => 'Edital de Licitação 07/2026 — Programa Igreja Legal',
                'modality' => 'Licitação pública',
                'procedure_type' => 'Concessão de direito real de uso social — CDRU-S',
                'process_number' => null,
                'opens_at' => '2026-04-01',
                'closes_at' => '2026-05-14',
                'published_on' => '2026-04-01',
                'deposit_deadline' => '2026-05-13',
                'proposal_deadline' => '2026-05-14',
                'auction_at' => '2026-05-14 10:00:00',
                'status' => 'in_result',
                'description' => 'Programa Igreja Legal para entidades religiosas e de assistência social. Os itens exibidos no mapa são referências históricas declaradas fracassadas.',
                'regions_summary' => 'Diversas regiões, incluindo Riacho Fundo II e Samambaia',
                'document_url' => 'https://www.terracap.df.gov.br/uploads/edicts/69dea8483f748.pdf',
                'official_page_url' => self::CHURCH_NOTICE_URL,
                'proposal_url' => null,
                'result_url' => self::CHURCH_NOTICE_URL,
            ],
        ];

        $notices = [];

        foreach ($definitions as $key => $attributes) {
            $notices[$key] = Notice::query()->updateOrCreate(
                ['code' => $attributes['code']],
                [...$attributes, 'public_visible' => true, 'source_checked_at' => self::CHECKED_AT, 'is_demo' => false]
            );
        }

        return $notices;
    }

    private function seedCategory(): BusinessCategory
    {
        return BusinessCategory::query()->updateOrCreate(
            ['slug' => 'templos-assistencia-social'],
            [
                'name' => 'Templos e assistência social',
                'description' => 'Referências públicas para entidades religiosas e de assistência social, sem cálculo de potencial.',
                'result_mode' => 'catalog',
                'aliases' => ['templo', 'templos', 'igreja', 'religioso', 'religiosa', 'entidade religiosa', 'assistencia social', 'assistência social', 'igreja legal'],
                'weights' => [],
                'active' => true,
            ]
        );
    }

    private function seedHistoricalLots(Notice $notice, BusinessCategory $category): void
    {
        $definitions = [
            [
                'code' => '819340-1',
                'title' => 'Referência histórica — Igreja Legal, item 18',
                'address' => 'SHRF II, QS 04, Conjunto 03, Lote 03',
                'area_sqm' => 925.49,
                'latitude' => -15.9045,
                'longitude' => -48.0335,
                'region_slug' => 'riacho-fundo-ii',
                'item_number' => '18',
                'appraisal_value' => 971000.00,
                'minimum_price' => 1456.50,
                'deposit_amount' => 4369.50,
            ],
            [
                'code' => '193308-6',
                'title' => 'Referência histórica — Igreja Legal, item 27',
                'address' => 'Samambaia, QS 307, Conjunto 03, Lote 05',
                'area_sqm' => 440.00,
                'latitude' => -15.8770,
                'longitude' => -48.0960,
                'region_slug' => 'samambaia',
                'item_number' => '27',
                'appraisal_value' => 437000.00,
                'minimum_price' => 655.50,
                'deposit_amount' => 1966.50,
            ],
        ];

        foreach ($definitions as $data) {
            $region = AdministrativeRegion::query()->where('slug', $data['region_slug'])->firstOrFail();
            $located = app(RegionLocator::class)->locate($data['latitude'], $data['longitude']);
            throw_unless($located?->is($region), "A coordenada aproximada do imóvel {$data['code']} deve estar dentro de {$region->name}.");

            $lot = Lot::query()->updateOrCreate(['code' => $data['code']], [
                'administrative_region_id' => $region->id,
                'title' => $data['title'],
                'address' => $data['address'],
                'area_sqm' => $data['area_sqm'],
                'zoning' => 'Programa Igreja Legal — referência histórica',
                'destination' => 'Entidade religiosa ou de assistência social, conforme condições do Edital 07/2026',
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'location_precision' => 'approximate',
                'status' => 'published',
                'offer_status' => 'failed',
                'source_url' => self::CHURCH_NOTICE_URL,
                'source_checked_at' => self::CHECKED_AT,
                'is_demo' => false,
                'is_featured' => false,
                'search_enabled' => true,
                'published_at' => self::CHECKED_AT,
            ]);

            if (DB::getDriverName() === 'pgsql') {
                DB::statement('UPDATE lots SET location = ST_SetSRID(ST_MakePoint(?, ?), 4326) WHERE id = ?', [$data['longitude'], $data['latitude'], $lot->id]);
            }

            NoticeItem::query()->updateOrCreate(
                ['notice_id' => $notice->id, 'item_number' => $data['item_number']],
                [
                    'lot_id' => $lot->id,
                    'amount_type' => 'monthly_public_price',
                    'minimum_price' => $data['minimum_price'],
                    'appraisal_value' => $data['appraisal_value'],
                    'deposit_amount' => $data['deposit_amount'],
                    'payment_terms' => 'Preço público mensal mínimo, conforme o Edital 07/2026.',
                    'outcome_notes' => 'Licitação fracassada. Sem disponibilidade atual para proposta.',
                    'status' => 'failed',
                ]
            );

            LotBusinessProfile::query()->updateOrCreate(
                ['lot_id' => $lot->id, 'business_category_id' => $category->id],
                [
                    'target_audience_score' => null,
                    'demand_density_score' => null,
                    'income_fit_score' => null,
                    'mobility_access_score' => null,
                    'opportunity_gap_score' => null,
                    'reasons' => [
                        'Referência pública real do Programa Igreja Legal.',
                        'Licitação fracassada e sem disponibilidade atual para proposta.',
                        'Consulte a Terracap sobre eventual nova oferta.',
                    ],
                ]
            );
        }
    }
}
