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
        <nav class="top-actions" aria-label="Acessos rápidos">
            <a href="#editais" class="notices-link">Editais vigentes <span x-text="currentNoticeCount" aria-label="quantidade vigente"></span></a>
            <a href="/admin" class="admin-link">Área administrativa <span aria-hidden="true">↗</span></a>
        </nav>
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
                <button class="catalog-chip" @click="search('templos e assistência social')">Templos e assistência social</button>
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
            <div class="legend" aria-hidden="true"><span class="legend-lot"></span> Lote demonstrativo <span class="legend-featured"></span> Recomendado <span class="legend-historical"></span> Referência pública histórica</div>
        </div>

        <aside class="side-panel" :class="{ 'is-open': selectedLot || recommendation || message }" aria-live="polite">
            <template x-if="!selectedLot && !recommendation && !message">
                <div class="empty-state">
                    <span class="empty-number">35</span>
                    <h2>Regiões para explorar</h2>
                    <p>Clique em uma região do mapa para isolá-la e conhecer os lotes demonstrativos disponíveis.</p>
                    <div class="empty-rule"></div>
                    <span>12 referências no protótipo</span>
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
                    <div class="detail-topline"><span class="demo-tag" :class="{ 'official-tag': !selectedLot.is_demo }" x-text="selectedLot.is_demo ? 'Demonstração' : 'Referência pública real'"></span><span x-text="selectedLot.code"></span></div>
                    <p class="eyebrow" x-text="selectedLot.region.name"></p>
                    <h2 x-text="selectedLot.title"></h2>
                    <p class="address" x-text="selectedLot.address"></p>

                    <template x-if="scoreFor(selectedLot.id)?.score !== null && scoreFor(selectedLot.id)?.score !== undefined">
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
                        <span x-text="selectedLot.is_demo ? 'Edital demonstrativo' : 'Fonte oficial'"></span>
                        <strong><span x-text="selectedLot.notice?.code"></span> · Item <span x-text="selectedLot.notice?.item"></span></strong>
                        <p><span x-text="selectedLot.notice?.amount_type === 'monthly_public_price' ? 'Preço público mensal mínimo' : 'Valor mínimo'"></span>: <b x-text="selectedLot.notice?.minimum_price ? currency.format(selectedLot.notice.minimum_price) : 'Não informado'"></b></p>
                        <p x-show="selectedLot.notice?.deposit_amount">Caução: <b x-text="currency.format(selectedLot.notice?.deposit_amount || 0)"></b></p>
                        <p x-show="selectedLot.notice?.outcome_notes" x-text="selectedLot.notice?.outcome_notes"></p>
                        <a x-show="selectedLot.notice?.official_page_url" :href="selectedLot.notice?.official_page_url" target="_blank" rel="noopener noreferrer">Consultar fonte oficial ↗</a>
                    </section>

                    <template x-if="scoreFor(selectedLot.id)?.score !== null && scoreFor(selectedLot.id)?.score !== undefined">
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
                    <template x-if="recommendation?.mode === 'catalog' && scoreFor(selectedLot.id)">
                        <section class="catalog-note"><h3>Catálogo informativo</h3><ul><template x-for="reason in scoreFor(selectedLot.id).reasons" :key="reason"><li x-text="reason"></li></template></ul></section>
                    </template>
                    <button class="request-button" type="button" @click="openRequest($event)">Simular requerimento</button>
                    <p class="disclaimer" x-text="selectedLot.disclaimer"></p>
                </article>
            </template>
        </aside>
    </section>

    <section id="editais" class="public-notices" aria-labelledby="notices-title">
        <div class="section-heading">
            <div><p class="eyebrow">Informação pública conferida</p><h2 id="notices-title">Editais e chamamentos</h2></div>
            <p>Consulte prazos e documentos na fonte oficial antes de qualquer providência.</p>
        </div>
        <p x-show="noticesLoading" aria-live="polite">Carregando editais…</p>
        <p x-show="noticesError" class="notices-error" x-text="noticesError" role="alert"></p>
        <div x-show="!noticesLoading && !noticesError && notices.length === 0" class="notices-empty">Nenhum edital público disponível neste momento.</div>
        <div x-show="notices.length" class="notices-table-wrap">
            <table class="notices-table">
                <caption class="sr-only">Editais publicados pela Terracap</caption>
                <thead><tr><th>Número</th><th>Modalidade</th><th>Situação</th><th>Prazo</th><th>Regiões</th><th></th></tr></thead>
                <tbody>
                    <template x-for="notice in notices" :key="notice.id">
                        <tr>
                            <td data-label="Número"><strong x-text="notice.code"></strong><small x-text="notice.data_nature === 'official_source' ? 'Fonte oficial' : 'Demonstração'"></small></td>
                            <td data-label="Modalidade" x-text="notice.modality"></td>
                            <td data-label="Situação"><span class="status-badge" :class="`status-${notice.status}`" x-text="noticeStatusLabel(notice.status)"></span></td>
                            <td data-label="Prazo" x-text="formatDate(notice.main_deadline)"></td>
                            <td data-label="Regiões" x-text="notice.regions || 'Consulte o edital'"></td>
                            <td><button type="button" class="detail-link" @click="openNotice(notice, $event)">Ver detalhes</button></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </section>

    <section class="accessible-list" aria-labelledby="opportunities-title">
        <div><p class="eyebrow">Alternativa textual ao mapa</p><h2 id="opportunities-title">Oportunidades visíveis</h2></div>
        <div class="lot-list">
            <template x-for="lot in visibleLots" :key="lot.id">
                <button @click="openLot(lot.id)"><span x-text="lot.region.name"></span><strong x-text="lot.title"></strong><small><span x-text="formatArea(lot.area_sqm)"></span> · ver detalhes →</small></button>
            </template>
        </div>
    </section>

    <dialog x-ref="noticeDialog" class="app-dialog" @cancel.prevent="closeNotice()">
        <article x-show="selectedNotice" class="dialog-card" aria-labelledby="notice-dialog-title">
            <button x-ref="noticeClose" type="button" class="dialog-close" @click="closeNotice()" aria-label="Fechar detalhes do edital">×</button>
            <p class="eyebrow">Fonte oficial · conferida em <span x-text="formatDate(selectedNotice?.source_checked_at?.slice(0, 10))"></span></p>
            <h2 id="notice-dialog-title" x-text="selectedNotice?.title"></h2>
            <p class="dialog-lead" x-text="selectedNotice?.description"></p>
            <dl class="notice-facts">
                <div><dt>Situação</dt><dd x-text="noticeStatusLabel(selectedNotice?.status)"></dd></div>
                <div><dt>Procedimento</dt><dd x-text="selectedNotice?.procedure_type || selectedNotice?.modality"></dd></div>
                <div><dt>Processo</dt><dd x-text="selectedNotice?.process_number || 'Não informado na página consultada'"></dd></div>
                <div><dt>Publicação</dt><dd x-text="formatDate(selectedNotice?.published_on)"></dd></div>
                <div><dt>Caução</dt><dd x-text="formatDate(selectedNotice?.deposit_deadline)"></dd></div>
                <div><dt>Propostas</dt><dd x-text="formatDate(selectedNotice?.proposal_deadline)"></dd></div>
                <div><dt>Licitação</dt><dd x-text="formatDate(selectedNotice?.auction_at, true)"></dd></div>
                <div><dt>Regiões</dt><dd x-text="selectedNotice?.regions || 'Consulte o edital'"></dd></div>
            </dl>
            <div class="official-links">
                <a x-show="selectedNotice?.links?.official_page" :href="selectedNotice?.links?.official_page" target="_blank" rel="noopener noreferrer">Página oficial ↗</a>
                <a x-show="selectedNotice?.links?.document" :href="selectedNotice?.links?.document" target="_blank" rel="noopener noreferrer">Documento ↗</a>
                <a x-show="selectedNotice?.links?.proposal" :href="selectedNotice?.links?.proposal" target="_blank" rel="noopener noreferrer">Canal de proposta ↗</a>
                <a x-show="selectedNotice?.links?.result" :href="selectedNotice?.links?.result" target="_blank" rel="noopener noreferrer">Resultado ↗</a>
            </div>
        </article>
    </dialog>

    <dialog x-ref="requestDialog" class="app-dialog request-dialog" @cancel.prevent="closeRequest()">
        <article class="dialog-card" aria-labelledby="request-dialog-title">
            <button type="button" class="dialog-close" @click="closeRequest()" aria-label="Fechar simulação de requerimento">×</button>
            <p class="eyebrow">Requerimento demonstrativo</p>
            <h2 id="request-dialog-title">Simular requerimento</h2>
            <div class="privacy-warning" role="alert"><strong>Simulação demonstrativa.</strong> Não informe dados pessoais reais. Este formulário não envia dados à Terracap e não gera protocolo oficial.</div>
            <template x-if="!requestReceipt">
                <form class="request-form" @submit.prevent="simulateRequest()">
                    <label>Nome<input x-ref="requestName" x-model="requestForm.name" required autocomplete="off" placeholder="Use um nome fictício"></label>
                    <label>CPF/CNPJ<input x-model="requestForm.document" required autocomplete="off" placeholder="Use dados fictícios"></label>
                    <label>Telefone<input x-model="requestForm.phone" required autocomplete="off" placeholder="(00) 00000-0000"></label>
                    <label>E-mail<input type="email" x-model="requestForm.email" required autocomplete="off" placeholder="exemplo@teste.invalid"></label>
                    <label class="wide">Endereço para correspondência<input x-model="requestForm.correspondenceAddress" required autocomplete="off" placeholder="Use um endereço fictício"></label>
                    <label>CEP<input x-model="requestForm.postalCode" required autocomplete="off" placeholder="00000-000"></label>
                    <label>Número do processo (opcional)<input x-model="requestForm.processNumber" autocomplete="off"></label>
                    <div class="wide readonly-lot"><span>Lote selecionado</span><strong><span x-text="selectedLot?.code"></span> · <span x-text="selectedLot?.address"></span></strong></div>
                    <label class="wide">Texto do requerimento<textarea x-model="requestForm.text" required rows="5" placeholder="Descreva uma solicitação fictícia para a demonstração"></textarea></label>
                    <label class="wide consent"><input type="checkbox" x-model="requestForm.acknowledgement" required> Estou ciente de que esta é apenas uma simulação local e não devo informar dados reais.</label>
                    <button class="request-button wide" type="submit">Simular envio</button>
                </form>
            </template>
            <template x-if="requestReceipt">
                <div class="request-receipt" x-ref="requestReceipt" tabindex="-1">
                    <span>Comprovante visual de simulação</span><strong x-text="requestReceipt"></strong>
                    <p>Nenhum dado foi enviado ou salvo. Este código não é protocolo da Terracap.</p>
                </div>
            </template>
            <div class="official-request-link">
                <a href="https://servicosonline.terracap.df.gov.br/gso-web-cliente/#/" target="_blank" rel="noopener noreferrer">Ir para o Requerimento Online oficial ↗</a>
                <small>Nenhum dado desta simulação será transferido ao portal oficial.</small>
            </div>
        </article>
    </dialog>

    <footer><span>Terracap Conecta · protótipo de inovação</span><span>Limites: IPEDF · editais: fontes oficiais indicadas · recomendações: demonstrativas</span></footer>
</main>
</body>
</html>
