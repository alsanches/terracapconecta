# Terracap Conecta — contexto de continuidade

Atualizado em: 2026-09-11

## Retomada rápida

```text
Estado atual: migration e carga oficial validadas; aplicação ativa ainda permanece na imagem anterior
Última etapa validada: execução idempotente e conferência direta da carga oficial em produção
Evidência: OfficialPublicDataSeeder executado duas vezes; 4 editais públicos distintos, 12 lotes no total, 2 históricos oficiais e 1 categoria religiosa em modo catalog; OFFICIAL_DATA_VERIFIED
Commit atual: release candidato da VPS em 231620a0cf72c705f9ea9c54092301bf24a34f02; continuidade segue avançando em origin/main
Ambiente: nova imagem disponível; app e queue continuam executando a imagem anterior 9a7b197; banco ainda não migrado
Próxima ação: apontar APP_IMAGE_TAG para a imagem candidata e recriar somente app e queue, preservando banco e backup
Bloqueios: nenhum; acesso SSH temporário e exclusivo está funcional
```

### Evidências da implantação em 11/09/2026

- Versão anterior ativa: tag Git/imagem `9a7b1974dd2b4cb4d9e6da0ae6ba68cf099ba01d`; ID da imagem `sha256:5b8750493280c94e66210e18731accab5ef27548b947408c270f22bc75ae6053`.
- Diretório de produção: `/opt/terracap-conecta`; arquivo `/opt/terracap-conecta/compose.production.yaml`.
- Contêineres anteriores saudáveis: app, queue, backup e PostgreSQL/PostGIS; somente o app publica `127.0.0.1:8011`.
- Backup anterior à migração: `/backups/terracap-conecta-predeploy-20260911T132458Z.dump`, 2,6 MB, SHA-256 `444dfa6f2cb80618c0a4001165e580ef7526d9210b27ec42d4d8a3581b108836`.
- `pg_restore --list` aprovou o catálogo; restauração integral em `terracap_restore_check_20260911` aprovou PostGIS 3.6.4, 35 RAs e 10 lotes; o banco temporário foi removido automaticamente.
- Nenhuma migration, seeder ou troca de imagem de produção havia sido executada até este ponto.
- Checkout da VPS confirmado limpo, sem branch ativa (detached HEAD), no SHA `9a7b1974dd2b4cb4d9e6da0ae6ba68cf099ba01d`; `origin` aponta para o repositório oficial do projeto.
- `.env.production` mantém `APP_IMAGE_TAG=9a7b1974dd2b4cb4d9e6da0ae6ba68cf099ba01d`; filesystem raiz possui 64 GB livres (33% utilizado).
- Primeira tentativa de checkout do commit documental `f91e951f055bdc7182e92d01cbf8423722b9650a` falhou com `unable to read tree`, compatível com clone raso/incompleto; HEAD permaneceu no release anterior e nada foi publicado.
- O release funcional imutável desta evolução é `5b7a51da000b2dc98ad64d70ae4007dd2af64b5e`; commits posteriores alteram apenas `PROJECT_CONTEXT.md` e não precisam compor a imagem.
- O fetch com `--depth=1 origin main` recuperou a árvore completa; checkout detached validado em `231620a0cf72c705f9ea9c54092301bf24a34f02`, com migration/seeder presentes e sem alterações locais.
- A imagem imutável `terracap-conecta:231620a0cf72c705f9ea9c54092301bf24a34f02` foi construída com sucesso, ID `sha256:952d66b88842bad4f0f125c71714fd20fafaf9be371f11c85dc173f72102b11b`, criada em 11/09/2026 às 14:59:16 -03:00 e com 225.337.581 bytes.
- O build Vite executado dentro da imagem foi aprovado e gerou `maplibre-gl-worker-DSF6dqVc.js`; os contêineres ativos permaneceram intocados na imagem anterior durante o build.
- A primeira chamada automatizada do build foi interrompida antes de qualquer construção porque uma variável foi expandida pelo PowerShell local; a repetição usou o SHA literal, eliminando a ambiguidade e produzindo `BUILD_OK`.
- O preflight executou a imagem candidata em contêineres efêmeros sem dependências recriadas: Laravel 13.30.1, PHP 8.4.25, ambiente `production`, debug desativado e manutenção desativada.
- A listagem de rotas confirmou as seis APIs esperadas em `/api/v1`, inclusive `notices`, `lots`, `regions` e `recommendations`.
- `migrate:status` confirmou que todas as migrations anteriores estavam executadas e apenas `2026_09_11_120000_expand_public_notices_and_catalog_lots` estava pendente; nenhum dado foi alterado no preflight (`CANDIDATE_PREFLIGHT_OK`).
- A migration incremental `2026_09_11_120000_expand_public_notices_and_catalog_lots` foi aplicada em 104,50 ms pela imagem candidata e confirmada no batch 2 como `Ran` (`MIGRATION_OK`). Nenhuma migration antiga foi reexecutada e o `DatabaseSeeder` não foi chamado.
- Somente `OfficialPublicDataSeeder` foi executado; uma segunda execução completa foi aprovada (`OFFICIAL_SEEDER_IDEMPOTENT_OK`), comprovando que a carga não duplica os registros.
- A verificação SQL direta confirmou quatro editais públicos com quatro códigos distintos: `07/2026` em resultado, `11/2026` encerrado, `12/2026` aberto e `CHAMAMENTO-01/2026` aberto.
- O banco contém 12 lotes, exatamente dois históricos oficiais (`193308-6` e `819340-1`), ambos fracassados e com localização aproximada; a categoria `templos-assistencia-social` existe uma única vez em modo `catalog` (`OFFICIAL_DATA_VERIFIED`).

## Planejamento aprovado em 11/09/2026

### Objetivo desta evolução

- Formulário demonstrativo de requerimento na ficha do lote, executado apenas no navegador e sem transmissão ou armazenamento de dados pessoais.
- Área pública de editais, com botão no topo, tabela responsiva, modal de detalhes e links para fontes oficiais.
- Administração ampliada de editais e itens, com publicação pública, prazos, situação, fontes e resultados.
- Categoria informativa `Templos e assistência social`, sem ranking, com dois imóveis históricos reais do Edital 07/2026 - Programa Igreja Legal.
- Dados oficiais e fictícios diferenciados em API, mapa, fichas, legendas e avisos.
- Implementação, testes, Git e atualização segura da produção sem alterar o fluxo do CAMP Conecta.

### Etapas e situação

- [VALIDADA] 1. Migration incremental, modelos e regras do domínio.
- [VALIDADA] 2. Seeder oficial separado e idempotente com editais e imóveis históricos.
- [VALIDADA] 3. Administração Filament ampliada.
- [VALIDADA] 4. APIs públicas de editais e catálogo informativo.
- [VALIDADA] 5. Tabela/modal de editais na página principal.
- [VALIDADA] 6. Atalho, busca e marcadores para templos e assistência social.
- [VALIDADA] 7. Formulário demonstrativo sem envio ao servidor.
- [VALIDADA] 8. Comunicação, acessibilidade e responsividade.
- [VALIDADA] 9. Testes PHP, Vite e Playwright.
- [EM EXECUÇÃO] 10. Commit, push, backup, publicação e validação dos dois sistemas. Git concluído; VPS aguarda autenticação.

### Decisões de implementação

- `PROJECT_CONTEXT.md` é o único arquivo canônico de continuidade e será atualizado após cada evidência validada.
- Editais terão publicação pública independente da situação; vigência considerará também o prazo, evitando contagem indevida após vencimento.
- Categorias terão modo `ranked` ou `catalog`; a categoria religiosa usará `catalog` e não exibirá nota ou fatores.
- Os itens 18 (`819340-1`, Riacho Fundo II) e 27 (`193308-6`, Samambaia) serão referências históricas reais, com resultado `fracassado`, sem disponibilidade atual e localização explicitamente aproximada.
- O formulário terá os campos do modelo fotografado, mas `Simular envio` não fará requisição HTTP, não salvará dados e gerará apenas comprovante visual `SIM-...`.
- Haverá link separado para o portal oficial; requerimento geral e proposta de licitação não serão apresentados como equivalentes.
- A carga oficial ficará em seeder próprio; o `DatabaseSeeder` geral não será executado novamente em produção.

### Fontes públicas conferidas

- Edital 12/2026 - CDRU Desenvolve/DF: caução até 22/09/2026 e licitação em 23/09/2026.
- Chamamento Público 01/2026 - Polo Agroindustrial do Rio Preto: propostas prorrogadas até 16/10/2026.
- Edital 11/2026 - Venda de Imóveis: recebimento em 11/09/2026; será mantido como encerrado.
- Edital 07/2026 - Programa Igreja Legal: licitação realizada em 14/05/2026 e em fase de resultados.
- Aviso republicado em 27/08/2026 declarou fracassados os itens 18 e 27 do Edital 07/2026.
- Portal oficial de serviços: `https://servicosonline.terracap.df.gov.br/gso-web-cliente/#/`.

### Critérios de aceite desta evolução

- As três buscas ranqueadas atuais continuam funcionando.
- Buscas religiosas retornam dois registros históricos, sem pontuação e com avisos/fontes corretos.
- Editais públicos podem ser listados e detalhados; vencidos deixam o contador de vigentes.
- Formulário simula validação e comprovante sem qualquer POST ou persistência.
- Modais funcionam por teclado, fecham com Escape e devolvem o foco.
- Administração cadastra e publica as novas informações com validações.
- Testes PHP, build Vite e Playwright aprovados; mapa e worker validados em navegador.
- Mudanças versionadas e publicadas; produção atualizada após backup, com Terracap e CAMP verificados.

### Marco validado 1 - domínio ampliado

- Migration incremental: `database/migrations/2026_09_11_120000_expand_public_notices_and_catalog_lots.php`.
- Modelos alterados: `Notice`, `NoticeItem`, `Lot` e `BusinessCategory`.
- Editais agora distinguem publicação pública, procedimento, processo, prazos, regiões, links oficiais, conferência, situação e origem.
- Itens suportam natureza do valor, avaliação, mínimo, caução e resultado.
- Lotes suportam precisão da localização, situação comercial e fonte; categorias suportam `ranked` e `catalog`; escores tornaram-se opcionais para catálogo.
- Vigência: edital só é vigente se estiver publicado, com situação `open` e prazo principal ainda não vencido. Prazo em data considera o fim do dia; licitação com horário preserva a hora.
- Evidências locais em 11/09/2026: `ExpandedDomainTest` aprovou 2 testes/8 asserções; suíte preexistente aprovou 13 testes/64 asserções; Pint aprovou os arquivos do marco.
- Próximo passo: criar o `OfficialPublicDataSeeder`, executá-lo duas vezes e provar idempotência, valores, situação e localização dos dois imóveis.

### Marco validado 2 - carga pública oficial

- Seeder isolado: `database/seeders/OfficialPublicDataSeeder.php`; ele não foi adicionado ao `DatabaseSeeder`.
- Quatro editais oficiais cadastrados por `updateOrCreate`: 12/2026, Chamamento 01/2026, 11/2026 e 07/2026.
- Categoria `templos-assistencia-social` criada em modo `catalog`, com sinônimos acentuados e não acentuados.
- Imóveis históricos reais: item 18/código `819340-1` em Riacho Fundo II e item 27/código `193308-6` em Samambaia.
- Ambos têm coordenadas aproximadas validadas dentro das respectivas RAs, situação `failed`, valores oficiais e aviso de indisponibilidade atual.
- O seeder executado duas vezes preservou exatamente quatro editais oficiais, dois lotes oficiais e os dez lotes demonstrativos.
- Evidência local em 11/09/2026: `OfficialPublicDataSeederTest` aprovou 2 testes/12 asserções.
- Próximo passo: expor apenas editais publicados nas APIs, implementar `mode=catalog` sem pontuação e ampliar os formulários administrativos.

### Marcos validados 3 e 4 - administração e APIs

- Filament: formulário, tabela, filtros e detalhe de editais receberam prazos, origem, publicação, fonte, links e novas situações; itens receberam tipo de valor, avaliação, caução e resultado.
- Lotes receberam precisão, situação comercial e fonte; perfis aceitam notas nulas para categorias de catálogo.
- Regras administrativas: edital oficial publicado exige página oficial e data de conferência; edital aberto exige prazo principal; links externos exigem HTTPS.
- Novas rotas: `GET /api/v1/notices` e `GET /api/v1/notices/{notice}`. A listagem expõe somente registros publicados e aceita filtros `status` e `current`.
- APIs de lotes distinguem `demonstration` e `official_reference`, com precisão, situação comercial, fonte e aviso específico.
- Recomendações retornam `mode=ranked` ou `mode=catalog`; catálogo religioso não possui nota ou fatores; busca ranqueada continua reproduzível.
- Evidências em 11/09/2026: conjunto integrado com 11 testes/65 asserções; validações administrativas com 2 testes/4 asserções; Pint aprovado.
- Próximo passo: integrar as APIs à página pública, implementar as duas modais acessíveis e provar que o formulário não envia dados.

### Marcos validados 5 a 9 - experiência pública e testes

- Topo: botão `Editais vigentes` com contador calculado pela vigência real e âncora para a seção pública.
- Editais: tabela desktop e cartões no celular, estado vazio/erro independente do mapa e modal com foco inicial, Escape, devolução de foco, datas, regiões e links oficiais com `noopener noreferrer`.
- Categoria religiosa: quarto atalho, sinônimos, dois marcadores roxos com legenda histórica, enquadramento conjunto e abertura do primeiro resultado; nenhum score, fator ou expressão de maior potencial.
- Ficha do imóvel: natureza do dado, precisão, situação, preço público mensal, caução, resultado, fonte e aviso próprio para referência histórica.
- Requerimento: modal com os campos aprovados, lote preenchido, aviso de privacidade, validação nativa, comprovante `SIM-...`, limpeza ao fechar e link separado para o portal oficial. Não há rota ou API de recebimento.
- Carregamento do mapa e de editais é independente; falha de um não esvazia o outro.
- `tests/browser/map.spec.js` cobre mapa/worker, três rankings, catálogo religioso, modal e foco, ausência de qualquer requisição não-GET, limpeza do formulário e cartões móveis.
- Evidências finais locais em 11/09/2026: 23 testes PHP e 123 asserções; Pint aprovado; `git diff --check` aprovado; Vite aprovado; 7 testes Playwright no Chrome aprovados; inspeção no navegador confirmou 35 RAs, 12 marcadores/listagens, quatro editais e contador 2.
- Próximo passo: revisar e versionar; em seguida inventariar a produção, gerar/validar backup e publicar a imagem imutável.

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
- Produção do Terracap ficará na mesma VPS do CAMP Conecta, com isolamento entre projetos.
- CAMP Conecta permanece fora do Docker e não terá seu fluxo de deploy alterado.
- Nginx e Certbot existentes no host continuam responsáveis pelas portas públicas 80/443 e pelo HTTPS.
- Terracap será publicado inicialmente em `https://terracap-conecta.179-198-101-134.sslip.io`.
- Backend HTTP do Terracap deverá ficar acessível somente pelo loopback do host, com `127.0.0.1:8011` como porta candidata já verificada livre.
- PostgreSQL/PostGIS do Terracap será exclusivo do projeto e não terá porta publicada no host.
- Volumes, rede Docker, segredos, cookies e backups do Terracap serão independentes do CAMP.
- O `compose.yaml` atual não pode ser executado inalterado na VPS porque publica 80/443.
- Todos os lotes, editais, valores e critérios fictícios devem ser identificados como demonstrativos.
- O arquivo `terracap-conecta.html` é referência visual e deve ser preservado.
- Não utilizar `latest` como referência operacional de rollback; registrar SHA Git, tag da imagem e digest/ID da imagem implantada.
- `DatabaseSeeder` não fará parte do deploy normal; sua execução será somente na carga inicial aprovada.
- A fila deverá ter `retry_after` maior que o timeout do worker.
- O serviço de scheduler não será iniciado na primeira implantação enquanto não houver tarefa agendada real.
- Logs da aplicação em contêiner devem preferencialmente ir para `stderr`, com rotação tratada pelo runtime.
- Backup PostgreSQL deverá detectar falha do dump, publicar somente arquivo válido e ser testado por restauração em banco isolado.

## Estado validado — aplicação

- Workspace inicialmente continha apenas `terracap-conecta.html`.
- PHP local 8.5.8, Composer 2.10.2 e Node.js 22.23.2 estão disponíveis.
- Laravel 13.30.1 foi criado a partir do projeto oficial e copiado para a raiz.
- Repositório Git local inicializado na branch `main`.
- Filament 5.7.8 instalado e painel administrativo registrado em `/admin`.
- MapLibre GL JS 6.7.0 e Alpine.js instalados no frontend.
- Identidade Git local configurada como `Alexandre Sanches <alsanches@gmail.com>`.
- Remoto `origin` conectado a `https://github.com/alsanches/terracapconecta.git`; o remoto inicialmente vazio já recebeu os commits do MVP.
- Commit funcional de referência e HEAD atual: `f5ee1891b19af0be77230f20781f72750ab7e4fa` (`fix: inclui worker cartografico e testa mapa no navegador`), publicado na branch `main`.
- GeoJSON oficial do IPEDF baixado para `database/data/ras-df.geojson`; validação confirmou exatamente 35 RAs em EPSG:4326.
- Domínio persistente criado para RAs, lotes, editais/itens, categorias, indicadores, fontes, sincronizações e auditoria.
- Carga demonstrativa validada no SQLite local: 35 RAs, dez lotes publicados e exatamente três lotes habilitados para busca.
- As coordenadas dos dez lotes foram validadas pelo localizador espacial e pertencem às RAs cadastradas.
- APIs versionadas registradas em `/api/v1` para regiões, lotes, ficha individual e recomendações.
- Site público map-first implementado com MapLibre, busca, atalhos, isolamento de RA, marcadores, painel explicativo, lista textual e gaveta móvel.
- Administração implementada em Filament para lotes, editais/itens, fontes, sincronizações e consulta das RAs; o acesso exige usuário administrador ativo.
- Cadastro de lote possui seletor cartográfico, conferência espacial da RA e impede publicação sem item de edital em oferta.
- Simulação de fontes e trilha de auditoria implementadas; nenhuma consulta externa é necessária durante a apresentação.
- Infraestrutura original descrita em `compose.yaml`: aplicação FrankenPHP/Caddy, fila, agendador, PostgreSQL/PostGIS e backup diário com retenção de sete dias.
- Perfis de ranking persistidos por categoria; os cinco pesos são lidos do banco e os fatores/justificativas podem ser mantidos no cadastro do lote.
- Suíte final em 03/09/2026: 13 testes aprovados, 64 asserções, formatação PHP aprovada e build Vite concluído.
- `compose.yaml` passou em validador YAML; as tags `dunglas/frankenphp:1-php8.4-bookworm` e `postgis/postgis:18-3.6` foram conferidas nas fontes oficiais.
- Docker não está instalado na estação Windows local.
- Nesta estação, a porta `8000` pertence ao CAMP Conecta; a pré-visualização local do Terracap usa `http://127.0.0.1:8010`.
- A execução HTTP local revelou que consultas SQLite com ordenação precisavam de arquivo temporário inacessível ao servidor. O SQLite local passou a usar `temp_store=MEMORY`; página, API e as 35 RAs foram validadas por HTTP na porta 8010.
- Falha de mapa vazio reproduzida em Chrome: o worker separado do MapLibre 6 não era incluído pelo build. Corrigido em `resources/js/map-engine.js` com importação `?worker&url`, compartilhada pelos mapas público e administrativo.
- Instâncias do mapa ficam fora do estado reativo Alpine; os dados enviados ao worker são objetos não reativos. O seletor administrativo também aguarda o GeoJSON antes de criar o mapa.
- Validação real em Chrome: contornos e dez marcadores renderizados; três testes Playwright aprovados para clique/retorno ao DF, as três buscas e gaveta móvel.

## Estado validado — VPS do CAMP

- Host: `srv1862758`.
- Sistema operacional: Debian GNU/Linux 13.6 (Trixie), amd64.
- IP público: `179.198.101.134`.
- VPS recebeu upgrade em 03/09/2026 e foi validada após reboot.
- Capacidade após upgrade: 2 vCPU, 7.8 GiB de RAM e aproximadamente 99 GB de disco.
- Após estabilização pós-reboot havia aproximadamente 6.7 GiB de RAM disponível e 72 GB livres no filesystem raiz.
- Swap permanece desabilitada.
- Nginx ocupa as portas públicas 80/443.
- MariaDB do CAMP escuta somente em `127.0.0.1:3306`.
- Serviços do CAMP validados ativos: `nginx`, `php8.4-fpm`, `mariadb`, `camp-conecta-queue`, `camp-conecta-telegram` e `camp-conecta-report-queue`.
- `https://campconecta.tech/` retornou HTTP 200 antes e depois das alterações de infraestrutura.
- HTTP do CAMP redireciona para HTTPS.
- Certbot está ativo e habilitado; renovação é gerenciada pelo host.
- Site Nginx do CAMP permanece em arquivo próprio e não foi alterado.
- Hostname `terracap-conecta.179-198-101-134.sslip.io` resolve para `179.198.101.134`.
- Porta `8011` foi verificada livre.
- Snapshot manual da VPS criado no hPanel em 03/09/2026 às 17:13, antes da instalação do Docker, com expiração indicada em 04/09/2026.
- Docker Engine foi instalado pelo repositório APT oficial da Docker, sem remoções ou upgrades de pacotes do CAMP.
- Docker instalado: 29.7.2.
- Docker Compose plugin instalado: 5.5.1.
- containerd instalado: 2.3.4.
- Serviços `docker` e `containerd` estão ativos e habilitados.
- Nenhum contêiner de aplicação foi criado até o momento.
- Após a instalação do Docker, `net.ipv4.ip_forward` passou de 0 para 1, comportamento esperado.
- `iptables` utiliza backend `nf_tables` via `iptables-nft`.
- Docker criou suas chains de firewall; política `FORWARD` passou a `DROP`, sem regressão observada no CAMP.
- Bridge padrão Docker: `172.17.0.0/16`, gateway `172.17.0.1`.
- Rede física do host: `179.198.101.0/24`; não há sobreposição com a bridge Docker.
- Docker usa `overlayfs`, cgroup v2, driver de cgroup `systemd` e raiz em `/var/lib/docker`.
- `/etc/docker/daemon.json` não existe; nenhuma customização global do daemon foi aplicada.
- Após a instalação do Docker, Nginx permaneceu como único processo nas portas 80/443, `8011` permaneceu livre, todos os serviços do CAMP ficaram ativos e `campconecta.tech` continuou retornando HTTP 200.

## Pendências técnicas antes de subir o Terracap

- Criar configuração de produção específica, preferencialmente `compose.production.yaml` completo, sem herdar as portas públicas do Compose original.
- Publicar somente `127.0.0.1:8011:80` no serviço web, após nova confirmação da porta.
- Definir `SERVER_NAME=:80` para o FrankenPHP/Caddy interno atrás do Nginx.
- Configurar trusted proxies do Laravel de forma restrita após validar o caminho real Nginx → contêiner.
- Fixar `APP_URL=https://terracap-conecta.179-198-101-134.sslip.io`.
- Definir cookie de sessão exclusivo e seguro para HTTPS.
- Ajustar `DB_QUEUE_RETRY_AFTER` para valor maior que o timeout de 120 segundos do worker.
- Rever Dockerfile para ativar `php.ini-production`, validar extensões/plataforma PHP 8.4, autoload e assets.
- Garantir `public/storage` e persistência de `storage/app/public`.
- Mudar healthcheck para `/up`, validando HTTP e banco.
- Preferir logs em `stderr`.
- Remover ou não iniciar o scheduler na primeira implantação enquanto não houver tarefas agendadas.
- Corrigir `docker/backup.sh` para detectar falha do dump, usar arquivo temporário/validação e publicar somente backup válido.
- Testar backup e restauração em banco isolado com PostGIS.
- Restringir variáveis/segredos por serviço; não reutilizar `.env`, APP_KEY, banco ou credenciais do CAMP.
- Não expor PostgreSQL no host.
- Validar build real da imagem com PHP 8.4 e PostgreSQL/PostGIS.
- Executar migrations e `DatabaseSeeder` somente em fluxo controlado e aprovado.
- Aplicar limites de CPU/memória aos serviços Terracap antes da publicação.
- Não declarar concluído até validar mapa, worker MapLibre, APIs, admin, upload/persistência, fila, backup/restauração e o CAMP após a ativação.

## Próximas ações

1. Atualizar e versionar a documentação de deploy sem alterar ainda os arquivos de infraestrutura.
2. Revisar e alterar, em commits separados e verificáveis, a infraestrutura do Terracap: Compose de produção, `.env.production.example`, Dockerfile, backup, healthcheck, fila e trusted proxies.
3. Validar `git diff`, testes locais e ausência de segredos antes do commit de infraestrutura.
4. Preparar imagem pelo SHA selecionado e validar o build real na VPS sem publicar o serviço em 80/443.
5. Inicializar PostgreSQL/PostGIS e executar migrations/carga inicial de forma controlada.
6. Disponibilizar o Terracap somente em loopback e testar `/up`, APIs, mapa e `/admin` antes de alterar Nginx.
7. Criar site Nginx exclusivo do Terracap, emitir certificado pelo mecanismo já existente e revalidar imediatamente o CAMP.
8. Validar persistência, fila, backup/restauração, limites de recursos e rollback.
9. Fazer aceite funcional completo em desktop/projetor e celular.

## Acesso local de demonstração

- URL nesta estação: `http://127.0.0.1:8010/admin`.
- Usuário local: `admin@terracapconecta.local`.
- A senha fica somente no arquivo `.env` ignorado pelo Git; não reutilizá-la na produção.

## Validações esperadas para aceite final

- Testes automatizados PHP aprovados.
- Build Vite aprovado.
- Imagem de produção criada e identificada por SHA/tag/digest.
- PostgreSQL 18/PostGIS 3.6 funcional com migrations reais.
- 35 RAs, dez lotes e três lotes pesquisáveis após carga inicial.
- APIs retornando os dados esperados.
- Três consultas principais produzindo resultados explicáveis e consulta desconhecida sem inventar recomendação.
- Rotas administrativas protegidas.
- Cadastro/publicação refletido no mapa.
- Worker MapLibre carregando HTTP 200 e mapa sem tela vazia.
- Upload de documento persistente após recriação do contêiner.
- Fila validada com job inofensivo controlado.
- Backup válido e restauração comprovada em banco isolado.
- Terracap acessível por HTTPS no hostname aprovado.
- CAMP Conecta continua íntegro, com serviços ativos e HTTP 200 após cada mudança relevante.
