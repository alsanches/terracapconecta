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
