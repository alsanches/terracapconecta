<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Protótipo Terracap Conecta: oportunidades demonstrativas no Distrito Federal.">
    <title>Terracap Conecta — mapa de oportunidades</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<main x-data="terracapMap" class="app-shell">
    <header class="topbar">
        <a href="/" class="brand" aria-label="Terracap Conecta — início">
            <span class="brand-mark" aria-hidden="true">TC</span>
            <span><strong>Terracap</strong><small>Conecta</small></span>
        </a>
        <div class="prototype-pill"><span></span> Protótipo demonstrativo</div>
        <a href="/admin" class="admin-link">Área administrativa <span aria-hidden="true">↗</span></a>
    </header>

    <section class="intro" aria-labelledby="page-title">
        <div>
            <p class="eyebrow">Distrito Federal · inteligência territorial</p>
            <h1 id="page-title">O lugar certo para<br><em>uma boa ideia.</em></h1>
        </div>
        <div class="intro-copy">
            <p>Explore oportunidades demonstrativas por região ou conte que tipo de negócio você deseja abrir.</p>
            <form class="search" @submit.prevent="search()" role="search">
                <label class="sr-only" for="business-search">Qual negócio você deseja abrir?</label>
                <input id="business-search" x-model="query" placeholder="Ex.: quero abrir um coworking" autocomplete="off">
                <button type="submit" :disabled="searching"><span x-text="searching ? 'Buscando…' : 'Encontrar'">Encontrar</span> <span aria-hidden="true">→</span></button>
            </form>
            <div class="quick-search" aria-label="Buscas sugeridas">
                <button @click="search('bar e gastronomia')">Bar e gastronomia</button>
                <button @click="search('coworking')">Coworking</button>
                <button @click="search('comércio e serviços essenciais')">Comércio e serviços</button>
            </div>
        </div>
    </section>

    <section class="workspace" aria-label="Mapa interativo de oportunidades">
        <div class="map-column">
            <div class="map-toolbar">
                <div>
                    <span class="toolbar-label">Explorando</span>
                    <strong x-text="selectedRegion ? selectedRegion.name : 'Todo o Distrito Federal'">Todo o Distrito Federal</strong>
                </div>
                <button x-show="selectedRegion || recommendation" x-cloak @click="resetMap()" class="back-button">← Voltar ao DF</button>
            </div>
            <div x-ref="map" class="map" aria-label="Mapa das 35 Regiões Administrativas do Distrito Federal"></div>
            <div x-show="loading" class="map-loading">Preparando as 35 regiões administrativas…</div>
            <div class="legend" aria-hidden="true"><span class="legend-lot"></span> Lote demonstrativo <span class="legend-featured"></span> Recomendado</div>
        </div>

        <aside class="side-panel" :class="{ 'is-open': selectedLot || recommendation || message }" aria-live="polite">
            <template x-if="!selectedLot && !recommendation && !message">
                <div class="empty-state">
                    <span class="empty-number">35</span>
                    <h2>Regiões para explorar</h2>
                    <p>Clique em uma região do mapa para isolá-la e conhecer os lotes demonstrativos disponíveis.</p>
                    <div class="empty-rule"></div>
                    <span>10 oportunidades no protótipo</span>
                </div>
            </template>

            <template x-if="message && !selectedLot">
                <div class="message-card">
                    <button class="close-panel" @click="message = ''; recommendation = null" aria-label="Fechar">×</button>
                    <p class="eyebrow">Resultado da busca</p>
                    <h2 x-text="message"></h2>
                    <template x-if="suggestions.length">
                        <div class="suggestions">
                            <p>Tente uma destas categorias:</p>
                            <template x-for="suggestion in suggestions" :key="suggestion">
                                <button @click="search(suggestion)" x-text="suggestion"></button>
                            </template>
                        </div>
                    </template>
                </div>
            </template>

            <template x-if="selectedLot">
                <article class="lot-detail">
                    <button class="close-panel" @click="selectedLot = null" aria-label="Fechar detalhes">×</button>
                    <div class="detail-topline"><span class="demo-tag">Demonstração</span><span x-text="selectedLot.code"></span></div>
                    <p class="eyebrow" x-text="selectedLot.region.name"></p>
                    <h2 x-text="selectedLot.title"></h2>
                    <p class="address" x-text="selectedLot.address"></p>

                    <template x-if="scoreFor(selectedLot.id)">
                        <section class="score-card">
                            <div class="score-value"><strong x-text="scoreFor(selectedLot.id).score"></strong><span>/100</span></div>
                            <div><b>Potencial demonstrativo</b><small>Ranking explicável para <span x-text="recommendation.category.name"></span></small></div>
                        </section>
                    </template>

                    <dl class="lot-facts">
                        <div><dt>Área</dt><dd x-text="formatArea(selectedLot.area_sqm)"></dd></div>
                        <div><dt>Zoneamento</dt><dd x-text="selectedLot.zoning"></dd></div>
                        <div class="wide"><dt>Destinação</dt><dd x-text="selectedLot.destination"></dd></div>
                    </dl>

                    <section class="notice" x-show="selectedLot.notice">
                        <span>Edital demonstrativo</span>
                        <strong><span x-text="selectedLot.notice?.code"></span> · Item <span x-text="selectedLot.notice?.item"></span></strong>
                        <p>Valor mínimo: <b x-text="selectedLot.notice?.minimum_price ? currency.format(selectedLot.notice.minimum_price) : 'Não informado'"></b></p>
                    </section>

                    <template x-if="scoreFor(selectedLot.id)">
                        <section class="factors">
                            <h3>Como chegamos a esta nota</h3>
                            <template x-for="factor in scoreFor(selectedLot.id).factors" :key="factor.key">
                                <div class="factor">
                                    <div><span x-text="factor.label"></span><b><span x-text="factor.contribution"></span> pts</b></div>
                                    <div class="factor-track"><i :style="`width:${factor.score}%`"></i></div>
                                    <small><span x-text="factor.weight"></span>% do ranking · nota <span x-text="factor.score"></span></small>
                                </div>
                            </template>
                            <h3>Leitura da oportunidade</h3>
                            <ul><template x-for="reason in scoreFor(selectedLot.id).reasons" :key="reason"><li x-text="reason"></li></template></ul>
                            <p class="source-note">Indicadores de demonstração, referência 2024. Fontes previstas: IPEDF/PDAD-A e Mobilidade GDF.</p>
                        </section>
                    </template>
                    <p class="disclaimer">Dados fictícios para demonstração. Não constituem inventário, oferta ou edital oficial da Terracap.</p>
                </article>
            </template>
        </aside>
    </section>

    <section class="accessible-list" aria-labelledby="opportunities-title">
        <div><p class="eyebrow">Alternativa textual ao mapa</p><h2 id="opportunities-title">Oportunidades visíveis</h2></div>
        <div class="lot-list">
            <template x-for="lot in visibleLots" :key="lot.id">
                <button @click="openLot(lot.id)"><span x-text="lot.region.name"></span><strong x-text="lot.title"></strong><small><span x-text="formatArea(lot.area_sqm)"></span> · ver detalhes →</small></button>
            </template>
        </div>
    </section>

    <footer><span>Terracap Conecta · protótipo de inovação</span><span>Limites territoriais: IPEDF · demais dados: fictícios</span></footer>
</main>
</body>
</html>
