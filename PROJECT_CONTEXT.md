# Terracap Conecta — contexto de continuidade

Atualizado em: 2026-09-03

## Objetivo

Construir um MVP demonstrativo para concurso de inovação tecnológica da Terracap: mapa interativo das 35 Regiões Administrativas do DF, dez lotes fictícios, três buscas explicáveis e administração de lotes, editais e fontes de dados.

## Decisões confirmadas

- Aplicação monolítica em PHP/Laravel, sem Next.js.
- Laravel 13 e Filament 5.
- Site público com Blade, Alpine.js, Tailwind CSS e MapLibre GL JS.
- PostgreSQL 18 com PostGIS 3.6 na produção.
- Mapa inicial estilizado, sem aparência de mapa viário convencional.
- Ao selecionar uma RA: enquadrar, destacar, filtrar lotes e oferecer retorno ao DF completo.
- Dez lotes demonstrativos; três pesquisáveis em Taguatinga, Águas Claras e Planaltina.
- Ranking determinístico de 0 a 100, sem IA generativa.
- Um administrador na primeira versão.
- Hospedagem em servidor Linux próprio, com Docker.
- Todos os lotes, editais, valores e critérios fictícios devem ser identificados como demonstrativos.
- O arquivo `terracap-conecta.html` é referência visual e deve ser preservado.

## Estado validado

- Workspace inicialmente continha apenas `terracap-conecta.html`.
- PHP local 8.5.8, Composer 2.10.2 e Node.js 22.23.2 estão disponíveis.
- Laravel 13.30.1 foi criado a partir do projeto oficial e copiado para a raiz.
- Repositório Git local inicializado na branch `main`.
- Filament 5.7.8 instalado e painel administrativo registrado em `/admin`.
- MapLibre GL JS 6.7.0 e Alpine.js instalados no frontend.
- Identidade Git local configurada como `Alexandre Sanches <alsanches@gmail.com>`.
- Remoto `origin` conectado a `https://github.com/alsanches/terracapconecta.git`; em 03/09/2026 ele não possuía branches publicadas.
- GeoJSON oficial do IPEDF baixado para `database/data/ras-df.geojson`; validação confirmou exatamente 35 RAs em EPSG:4326.
- Domínio persistente criado para RAs, lotes, editais/itens, categorias, indicadores, fontes, sincronizações e auditoria.
- Carga demonstrativa validada no SQLite local: 35 RAs, dez lotes publicados e exatamente três lotes habilitados para busca.
- As coordenadas dos dez lotes foram validadas pelo localizador espacial e pertencem às RAs cadastradas.
- APIs versionadas registradas em `/api/v1` para regiões, lotes, ficha individual e recomendações.
- Git local usa a branch `main`, a identidade informada pelo usuário e o remoto vazio `github.com/alsanches/terracapconecta` como `origin`.
- Site público map-first implementado com MapLibre, busca, atalhos, isolamento de RA, marcadores, painel explicativo, lista textual e gaveta móvel.
- Administração implementada em Filament para lotes, editais/itens, fontes, sincronizações e consulta das RAs; o acesso exige usuário administrador ativo.
- Cadastro de lote possui seletor cartográfico, conferência espacial da RA e impede publicação sem item de edital em oferta.
- Simulação de fontes e trilha de auditoria implementadas; nenhuma consulta externa é necessária durante a apresentação.
- Infraestrutura descrita em `compose.yaml`: aplicação FrankenPHP/Caddy, fila, agendador, PostgreSQL/PostGIS e backup diário com retenção de sete dias.
- Perfis de ranking persistidos por categoria; os cinco pesos são lidos do banco e os fatores/justificativas podem ser mantidos no cadastro do lote.
- Suíte final em 03/09/2026: 13 testes aprovados, 64 asserções, formatação PHP aprovada e build Vite concluído.
- `compose.yaml` passou em validador YAML; as tags `dunglas/frankenphp:1-php8.4-bookworm` e `postgis/postgis:18-3.6` foram conferidas nas fontes oficiais.
- Docker não está instalado nesta estação; a criação real dos contêineres e o teste contra PostgreSQL/PostGIS permanecem para uma máquina com Docker.
- Primeiro marco funcional versionado no commit `b5ae687` (`feat: implementa MVP Terracap Conecta`) e publicado na branch `main` do GitHub.
- Nesta estação, a porta `8000` já pertence ao CAMP Conecta; a pré-visualização do Terracap Conecta deve usar `http://127.0.0.1:8010`.
- A execução HTTP local revelou que consultas SQLite com ordenação precisavam de arquivo temporário inacessível ao servidor. O SQLite local passou a usar `temp_store=MEMORY`; página, API e as 35 RAs foram validadas por HTTP na porta 8010.
- Falha de mapa vazio reproduzida em Chrome: o worker separado do MapLibre 6 não era incluído pelo build. Corrigido em `resources/js/map-engine.js` com importação `?worker&url`, compartilhada pelos mapas público e administrativo.
- Instâncias do mapa ficam fora do estado reativo Alpine; os dados enviados ao worker são objetos não reativos. O seletor administrativo também aguarda o GeoJSON antes de criar o mapa.
- Validação real em Chrome: contornos e dez marcadores renderizados; três testes Playwright aprovados para clique/retorno ao DF, as três buscas e gaveta móvel. O usuário deve atualizar a página da porta 8010 com Ctrl+F5 após o novo build.

## Próximas ações

1. Validar a criação real dos contêineres e a migration PostGIS em uma máquina com Docker disponível.
2. Fazer ensaio visual no navegador/projetor e ajustar conteúdo de apresentação.

## Acesso local de demonstração

- URL nesta estação: `http://127.0.0.1:8010/admin`
- Usuário local: `admin@terracapconecta.local`
- A senha fica somente no arquivo `.env` ignorado pelo Git; trocá-la antes de qualquer publicação.

## Validações esperadas

- Testes automatizados PHP aprovados.
- Build Vite aprovado.
- APIs retornando 35 RAs e dez lotes.
- Três consultas principais produzindo resultados explicáveis.
- Rotas administrativas protegidas.
- Cadastro/publicação refletido no mapa.
- Simulação de fonte registrada no histórico.
