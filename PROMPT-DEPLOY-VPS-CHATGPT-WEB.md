# Prompt de continuidade — implantação do Terracap Conecta na VPS do CAMP Conecta

Preparado em 03/09/2026 a partir dos arquivos locais dos dois projetos. **Nenhum acesso SSH nem alteração na VPS foi realizado na preparação deste documento.**

Copie o conteúdo abaixo para uma nova conversa do ChatGPT Web, ou anexe este arquivo e peça: “Siga este prompt e comece pela primeira etapa, somente de diagnóstico”. Anexe também os arquivos não sigilosos indicados ao final.

---

## 1. Seu papel e o que eu preciso

Você será meu orientador técnico para colocar o **Terracap Conecta** em funcionamento na **mesma VPS Linux que já hospeda o CAMP Conecta**, preservando o funcionamento do CAMP.

Sou Alexandre Sanches. Trabalho no Windows com PowerShell. Você deve me orientar com comandos curtos e copiáveis, uma etapa por vez, aguardar o resultado e só então indicar a próxima etapa. Explique o objetivo em linguagem simples, identificando onde executar: **PowerShell local** ou **terminal Linux conectado por SSH**. Não misture as sintaxes.

Quero uma instalação persistente, com domínio próprio ou subdomínio a definir, HTTPS, banco PostgreSQL/PostGIS, painel administrativo, mapa, fila, agendador, backups e procedimento de atualização/retorno de versão.

Não presuma que você tem acesso aos meus arquivos locais, à VPS ou ao conteúdo de um repositório privado. Peça arquivos e saídas sanitizadas quando necessário. Se houver ferramentas de execução conectadas, comece somente por leituras; apresente a proposta e aguarde minha autorização antes de modificar a infraestrutura.

**Prioridade máxima: não interromper, sobrescrever, reconfigurar indevidamente ou misturar dados com o CAMP Conecta.**

## 2. Regras obrigatórias da condução

1. Primeiro inventarie o servidor sem alterações. Não instale Docker, pacotes, certificados nem execute o Compose antes de conhecer o ambiente e obter minha aprovação da arquitetura.
2. Não use `git pull` dentro da release ativa do CAMP. Não altere o seu link `current`, arquivos compartilhados, banco, `.env`, serviços de fila/Telegram ou procedimento de implantação.
3. Não execute o script de deploy do CAMP para publicar o Terracap. São projetos independentes.
4. Não troque o proxy público nem pare Nginx/Apache/Caddy para liberar as portas 80/443. Não reinicie globalmente Docker, PHP, MariaDB ou a VPS como tentativa de correção.
5. Não use `docker compose down -v`, limpezas globais do Docker, exclusão de volumes, `migrate:fresh`, `db:wipe`, rollback destrutivo de banco ou comandos recursivos sobre diretórios amplos.
6. Não solicite que eu cole senhas, chaves privadas, tokens, `APP_KEY` ou arquivos `.env` completos. Evite também saídas completas de `docker inspect`, `docker compose config`, `printenv` e configurações que possam conter segredos. Prefira consultas específicas ou validação silenciosa.
7. Mudanças em proxy, DNS, firewall, instalação de pacotes e permissões exigem explicação do impacto, cópia da configuração anterior e autorização. Preserve minha conexão SSH e as regras atuais.
8. Antes de gravar ou mover arquivos, confirme diretório absoluto, projeto, usuário e alvo. Não use variáveis genéricas perigosas como `$HOME` para operações de arquivos no PowerShell.
9. Se houver erro, interrompa a sequência e diagnostique. Não envie uma cadeia de comandos que continue depois da falha.
10. Diferencie sempre **verificado no código**, **testado localmente**, **hipótese a confirmar na VPS** e **validado em produção**.
11. Consulte documentação oficial atual para detalhes sensíveis a versão. Não atualize todas as dependências para resolver um problema de instalação; preserve os arquivos de lock e faça mudanças pontuais, versionadas e testadas.
12. Não considere a implantação concluída porque a página HTML responde. Precisamos verificar mapa no navegador, autenticação, banco espacial, persistência, processos e restauração de backup.

## 3. Projeto, repositório e ponto de partida

- Projeto: Terracap Conecta, MVP demonstrativo para concurso de inovação tecnológica.
- Repositório: https://github.com/alsanches/terracapconecta
- Remoto Git: `https://github.com/alsanches/terracapconecta.git`.
- Branch: `main`.
- Identidade Git: `Alexandre Sanches <alsanches@gmail.com>`.
- Pasta local: `C:\Users\alexandre.sanches_ag\Documents\TERRACAP-CONECTA`.
- Contexto atualizado: `PROJECT_CONTEXT.md`.
- Commit funcional de referência: `f5ee1891b19af0be77230f20781f72750ab7e4fa`, que corrigiu o worker cartográfico e adicionou testes no navegador.
- Este prompt pode estar em um commit documental posterior. Confirme o SHA completo que será realmente implantado; não presuma que o HEAD continua o mesmo.
- Não publique uma cópia com alterações locais não revisadas. Registre commit, tag da imagem e, quando disponível, digest.

O HTML antigo `terracap-conecta.html` é somente referência visual. **A aplicação é Laravel na raiz do repositório**, não esse HTML isolado.

## 4. Ambiente local confirmado do Terracap

- Windows e PowerShell.
- PHP 8.5.8 disponível em `C:\Users\alexandre.sanches_ag\Documents\CAMP\.runtime\php\php.exe`.
- Composer 2.10.2, Node.js 22.23.2 e npm 10.9.8.
- Docker não está instalado nesta estação.
- Desenvolvimento local usa SQLite; produção planejada usa PostgreSQL/PostGIS.
- Terracap local: `http://127.0.0.1:8010` e `http://127.0.0.1:8010/admin`.
- A porta local 8000 pertence ao CAMP; não encerrar esse processo.
- O administrador local usa `admin@terracapconecta.local`. Sua senha é apenas de desenvolvimento, está no `.env` ignorado e **não deve ser reutilizada na VPS**.
- A correção SQLite `temp_store=MEMORY` resolveu falha de consultas HTTP locais. Não é uma configuração a transportar para PostgreSQL.

Validações já registradas em 03/09/2026:

- 13 testes PHP aprovados, 64 asserções, formatação PHP aprovada e build Vite concluído.
- Três testes Playwright aprovados: seleção/retorno de RA, três buscas e painel móvel.
- Navegador Chrome mostrou contornos e dez marcadores após correção do worker.
- GeoJSON com exatamente 35 RAs validado; dez lotes demonstrativos carregados no SQLite.
- YAML do Compose validado sintaticamente.

**Ainda não validados:** build Docker real, execução com PHP 8.4 dentro da imagem, migrations/seeder contra PostgreSQL/PostGIS, publicação na VPS, configuração do proxy, backup/restauração real e capacidade do servidor compartilhado. Não apresente os testes SQLite como prova de funcionamento no PostgreSQL.

## 5. O que sabemos sobre o CAMP e o que falta confirmar

Foram lidos scripts locais em:

`C:\Users\alexandre.sanches_ag\Documents\CAMP-CONSOLIDACAO\camp-conecta\tools\deploy\`

Eles descrevem o seguinte ambiente esperado, **mas isso ainda precisa ser confirmado por leitura na VPS**:

- Alias SSH padrão: `campconecta`.
- Domínio padrão: `https://campconecta.tech`.
- Base: `/var/www/camp-conecta`.
- Releases: `/var/www/camp-conecta/releases`.
- Link da aplicação ativa: `/var/www/camp-conecta/current`, apontando para uma pasta `releases/<identificador>/camp-conecta`.
- Compartilhados: `/var/www/camp-conecta/shared/.env` e `/var/www/camp-conecta/shared/storage`.
- Binário PHP esperado pelo script: `/usr/bin/php`.
- Serviços esperados: `nginx.service`, `php8.4-fpm.service`, `mariadb.service`, `camp-conecta-queue.service` e `camp-conecta-telegram.service`.
- Há verificação condicional de `camp-conecta-report-queue.service`.
- O script local `tools/deploy/deploy.ps1` usa os modos `Preflight`, `Prepare` e `Activate`; o fluxo de ativação chama a verificação posterior. Exige SHA completo e versão esperada.
- O processo do CAMP usa releases imutáveis e compartilhados. Preserve-o integralmente.

Ainda não sabemos: IP efetivo, usuário/porta SSH, disponibilidade de sudo, distribuição/versão Linux, arquitetura, memória, CPU, espaço livre, swap, Docker/Compose instalado, regras de firewall, proxy realmente ativo, gerenciamento de certificados, provedor DNS e domínio desejado do Terracap.

Não transforme valores dos scripts locais em fatos atuais sem conferência. Não é necessário ler o `.env` do CAMP para configurar o Terracap.

## 6. Arquitetura real do Terracap

- Monólito **PHP + Laravel 13.30.1 + Filament 5.7.8**.
- PHP de produção planejado: **8.4**. Como a instalação local usou 8.5.8, verificar compatibilidade efetiva do lock com 8.4.
- Frontend: Blade, Alpine.js 3.17.1, Tailwind CSS e Vite.
- Cartografia: MapLibre GL JS 6.7.0; Konva ficou fora deste MVP.
- Banco alvo: PostgreSQL 18 + PostGIS 3.6, imagem `postgis/postgis:18-3.6`.
- Execução web prevista: FrankenPHP/Caddy, imagem-base `dunglas/frankenphp:1-php8.4-bookworm`.
- Fila, cache e sessões: banco de dados, sem necessidade de Redis no MVP.
- Documentos: disco Laravel `public`, com persistência e link público a validar.
- Não existe frontend Next.js, serviço Python, IA generativa ou integração real obrigatória para a apresentação.
- Os testes PHP atuais utilizam PHPUnit; Pest constava do plano, mas não deve ser pressuposto como já instalado.

Funcionalidades implementadas:

- `/` abre a experiência de mapa com 35 RAs, busca e atalhos.
- Clique na RA enquadra/destaca a região e filtra os lotes; há retorno ao DF.
- Dez lotes fictícios publicados; três habilitados para busca: gastronomia em Taguatinga, coworking em Águas Claras e comércio/serviços essenciais em Planaltina.
- Sete lotes adicionais acessíveis pela exploração manual em Plano Piloto, Guará, Ceilândia, Samambaia, Gama, Sobradinho e Santa Maria.
- Painel lateral com informações do lote e demonstração; gaveta inferior no celular.
- Busca determinística com sinônimos e ranking de cinco fatores, pesos 30/25/20/15/10. Busca desconhecida não deve inventar recomendação.
- `/admin` em Filament: lotes, editais/itens, fontes, sincronizações, consulta de RAs e dashboard. Exige administrador ativo.
- Cadastro de lote com seletor no mapa, conferência da RA e validação antes de publicar.
- Auditoria e simulação de fontes; sem conectar sistemas reais do GDF nesta implantação.

Endpoints:

- `GET /api/v1/regions`: GeoJSON das regiões.
- `GET /api/v1/lots`: lotes publicados; resposta com `data` e `meta.count`.
- `GET /api/v1/lots/{id}`: ficha de um lote existente.
- `GET /api/v1/recommendations?query=...`: categoria, resultados e explicação.
- `GET /up`: saúde Laravel com consulta ao banco (`select 1` no evento `DiagnosingHealth`).

O arquivo `database/data/ras-df.geojson`, cerca de 2,9 MB, contém os limites oficiais; precisa estar na imagem para a carga inicial. Lotes, editais, valores e indicadores de demonstração são fictícios, ainda que façam referência a fontes institucionais. Não os apresente como inventário oficial ou dados socioeconômicos verificados.

## 7. Convivência proposta na VPS — confirmar após inventário

Se o Nginx do host estiver realmente servindo o CAMP, a proposta preferencial é:

```text
Internet :443
      |
Nginx existente + certificados gerenciados no host
      |-- domínio CAMP ------> aplicação CAMP atual, intacta
      `-- domínio Terracap --> 127.0.0.1:<porta-livre>
                                    |
                               contêiner app :80
                                    |
                         rede Docker do Terracap
                             |-- PostgreSQL/PostGIS
                             |-- fila
                             |-- agendador
                             `-- backup
```

- Domínio/subdomínio Terracap deve ser escolhido por mim. Não invente que já existe.
- Uma porta de loopback, por exemplo **8011**, é apenas candidata; verificar se está livre na VPS.
- O contêiner Terracap não deve disputar 80/443 públicos com o proxy atual.
- Caddy interno recebe HTTP, com `SERVER_NAME=:80`; o endereço externo Laravel usa `APP_URL=https://<dominio-aprovado>`.
- O proxy existente termina TLS e encaminha host e protocolo. Configurar proxies confiáveis no Laravel de maneira restrita, compatível com o tráfego real da rede Docker. Não simplesmente confiar em qualquer origem.
- Banco sem porta publicada no host. Não usar o MariaDB do CAMP nem credenciais compartilhadas.
- Diretórios, projeto Compose, volumes, rede, cookies e backups exclusivos do Terracap.
- Se o proxy estiver em contêiner, o loopback dele não é o do host: revisar o desenho para rede privada adequada, sem conectar o banco a uma rede pública de proxy.
- Se a VPS não tiver capacidade suficiente, explicar as evidências e pedir decisão; não instalar serviços pesados mesmo assim.

Referência para publicação de portas e ressalvas de versões antigas do Docker: [documentação oficial de portas Docker](https://docs.docker.com/engine/network/port-publishing/).

## 8. Revisão obrigatória dos arquivos de infraestrutura antes de executar

Os arquivos atuais são um ponto de partida, **não uma configuração homologada para coexistência com CAMP**. Peça as versões atuais e proponha alterações versionadas no Terracap.

### 8.1 Compose e porta pública

`compose.yaml` tem nome `terracap-conecta`, serviços `app`, `queue`, `scheduler`, `db`, `backup` e volumes `app-storage`, `postgres-data`, `caddy-data`, `caddy-config`, `database-backups`.

**Problema confirmado:** o serviço `app` publica `80:80`, `443:443` e `443:443/udp`. Não subir esse arquivo inalterado na VPS compartilhada.

Prepare uma configuração de coexistência. Se usar override, atenção: listas de portas podem ser mescladas, conservando as entradas originais. Valide a configuração efetiva sem imprimir segredos; um arquivo separado completo pode ser mais claro. Não use recursos como `!override` sem verificar suporte na versão instalada. [Regras oficiais de mesclagem do Compose](https://docs.docker.com/compose/how-tos/multiple-compose-files/merge/).

O arquivo usa `env_file: .env.production`, mas isso injeta variáveis nos contêineres; não garante, sozinho, a interpolação de `${APP_IMAGE_TAG}` pelo Compose. Defina explicitamente a origem de interpolação e confira a imagem resolvida sem divulgar o ambiente inteiro. Fixe o nome do projeto e os volumes entre releases para não criar acidentalmente um banco novo vazio.

### 8.2 Imagem PHP e assets

O Dockerfile tem estágio Node 22 para `npm ci && npm run build`, estágio `composer:2` para dependências PHP e imagem final FrankenPHP com PHP 8.4. Instala explicitamente `pdo_pgsql`, `intl`, `zip`, `opcache`, `pcntl`.

Revisar e testar:

- O Composer é executado em estágio diferente da imagem PHP final. Confirmar PHP/extensões ali e no runtime. Preferir resolver a instalação com o runtime alvo corretamente equipado; não mascarar com `--ignore-platform-reqs`.
- Verificar todas as extensões exigidas por Laravel/Filament e o `composer.lock`, não somente a lista explícita do Dockerfile.
- Confirmar o autoload otimizado após copiar o código da aplicação; o estágio de dependências recebe inicialmente apenas `composer.json` e `composer.lock`.
- Assets precisam existir antes dos comandos Artisan que carregam o painel Filament e consultam o manifesto Vite.
- A imagem não pode conter `.env` local, banco SQLite, credenciais, logs pessoais ou `public/hot`.
- O `.dockerignore` exclui testes; rodar testes em estágio/ambiente apropriado, não pressupor suíte disponível na imagem de produção `--no-dev`.
- Não executar servidor Vite de desenvolvimento na VPS. Entregar `public/build` pronto e preservar o manifesto, JS, CSS e worker.
- Preferir imagem identificada pelo SHA, sem depender de `latest`. Se o build pressionar a VPS, propor build fora dela/CI, sem criar registry ou publicar imagem com código sem minha aprovação.

### 8.3 Mapa: não reintroduzir a tela vazia

O MapLibre 6 usa worker separado. `resources/js/map-engine.js` importa `maplibre-gl/dist/maplibre-gl-worker.mjs?worker&url` e chama `setWorkerUrl`; mapas público e administrativo compartilham esse módulo.

Preserve essa correção, a instância do mapa fora do estado reativo Alpine e a carga de GeoJSON antes de criar o seletor administrativo. Verifique no navegador o carregamento HTTP 200 do arquivo de worker gerado e ausência de bloqueios de conteúdo/CSP/MIME. Uma API funcional com painel de mapa vazio é falha de aceite.

### 8.4 Saúde, arquivos e cache

- O healthcheck atual do Compose executa `php artisan about --only=environment`: não prova HTTP nem banco. Adequar a verificação ao `/up`, com tempo para inicialização e ferramenta disponível na imagem.
- Healthcheck não substitui teste de fila, agendador e funcionalidade. Nem toda configuração Docker reinicia automaticamente contêiner apenas por estar `unhealthy`.
- Confirmar permissões de `storage` e `bootstrap/cache` para o usuário efetivo; nunca recorrer a `chmod 777`.
- Persistência atual monta `app-storage` em `/app/storage/app`. Confirmar documentos públicos, link `public/storage`, visibilidade pretendida e acesso após recriação do app.
- Definir destino/rotação de logs; arquivos de log fora do volume desaparecem ao recriar contêineres.
- Gerar caches Laravel com o ambiente correto de produção, não embutir segredos no build. Conferir comandos suportados, incluindo otimizações do Filament quando aplicáveis. [Implantação oficial do Laravel](https://laravel.com/framework/docs/13.x/deployment).

### 8.5 Fila e agendador

- Fila atual: `queue:work --sleep=2 --tries=3 --timeout=120`.
- `config/queue.php` usa `DB_QUEUE_RETRY_AFTER`, padrão 90 segundos. Corrigir para intervalo superior ao timeout de 120, por exemplo 180 após avaliação; o timeout deve ser menor que `retry_after` para evitar reexecução concorrente. [Documentação oficial de filas Laravel](https://laravel.com/framework/docs/13.x/queues).
- Agendador atual: `schedule:work`. Deve existir somente uma instância agendadora para essa instalação; não duplicar também um cron equivalente.
- Atualizar/recriar trabalhadores para a mesma imagem da aplicação; prever parada graciosa.
- A ação administrativa de simulação atualmente usa `dispatchSync`: sucesso dela não comprova consumo da fila. Testar a fila com job inofensivo controlado e conferir falhas; não acionar e-mail/Telegram ou órgãos reais.

### 8.6 Backup — corrigir antes de confiar

`docker/backup.sh` faz `pg_dump | gzip`, salva no volume `/backups`, apaga arquivos antigos e repete a cada 24 horas.

Risco confirmado na estrutura: `/bin/sh` com `set -eu` não garante detectar falha do primeiro programa do pipeline. Pode sobrar um gzip sem dump válido. Proponha tratamento que capture a falha do dump, grave arquivo temporário e só publique o backup após sucesso, com retenção executada apenas depois de backup válido.

- Usar ferramentas compatíveis com PostgreSQL 18.
- Reter ao menos sete dias de backups válidos e ter backup antes das migrations de atualização.
- Backup no mesmo disco da VPS não é recuperação de desastre. Perguntar destino externo, criptografia, acesso e política; não cadastrar serviço pago sem autorização.
- Incluir documentos persistentes e plano seguro para recuperar configuração/chave, sem expor segredos no Git.
- Restaurar um dump em banco isolado de teste com PostGIS. Não testar restauração por cima do banco ativo.
- Testar também cenário de falha do backup e como ela será percebida; definir monitoramento simples, sem inventar alertas já configurados.

## 9. Variáveis e segredos de produção

Use `.env.production.example` como lista inicial, nunca a `.env` local como arquivo pronto. O arquivo real deve ficar fora do Git e protegido no servidor.

Revisar pelo menos:

- `APP_NAME`, `APP_ENV=production`, `APP_DEBUG=false`.
- `APP_KEY`: nova para Terracap, persistida com segurança e estável entre releases. Não regenerar a cada deploy; não copiar a chave do CAMP.
- `APP_URL=https://<dominio-confirmado>`.
- `SERVER_NAME=:80` para o cenário de proxy externo aprovado.
- `APP_IMAGE_TAG=<sha-ou-tag-imutavel>` e origem de interpolação Compose.
- `APP_LOCALE`/`APP_FALLBACK_LOCALE=pt_BR`; política de horário e timezone confirmada no código, sem inventar variável que a aplicação não lê.
- `DB_CONNECTION=pgsql`, `DB_HOST=db`, `DB_PORT=5432`.
- `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` exclusivos do Terracap.
- `POSTGRES_DB`, `POSTGRES_USER`, `POSTGRES_PASSWORD` compatíveis com o provisionamento adotado. Considerar papel de aplicação restrito e provisionamento privilegiado separado para extensões/migrations; não confundir senha de bootstrap com senha do usuário da aplicação.
- `SESSION_DRIVER=database`, `CACHE_STORE=database`, `QUEUE_CONNECTION=database`.
- `DB_QUEUE_RETRY_AFTER` compatível com o timeout do trabalhador.
- `FILESYSTEM_DISK=public` se mantido o comportamento atual.
- Cookie de sessão exclusivo do Terracap, seguro sob HTTPS e sem ampliar domínio de cookie para abranger indevidamente o CAMP.
- `LOG_CHANNEL`/`LOG_LEVEL`, destino e rotação.
- `ADMIN_NAME`, `ADMIN_EMAIL`, `ADMIN_PASSWORD` novos para a carga inicial, sem divulgar seus valores no chat.

No Compose atual, banco e backup recebem o mesmo arquivo inteiro de ambiente do app. Proponha restringir os segredos de cada serviço ao necessário. Senhas devem ser geradas/definidas de forma que não apareçam em comandos registrados no histórico ou na listagem de processos.

Mudança de `POSTGRES_PASSWORD` no ambiente não altera automaticamente a senha de um banco já inicializado. Se houver volume pré-existente, diagnosticar e fazer rotação controlada, jamais apagar o volume para “aplicar a senha”.

O seeder lê `ADMIN_*` diretamente do ambiente. Garanta disponibilidade na primeira carga; depois da criação aprovada, remova a senha de bootstrap dos serviços que não precisam dela sem trocar acidentalmente a senha do usuário existente.

## 10. Banco espacial e carga inicial

Tabelas de domínio: `administrative_regions`, `lots`, `notices`, `notice_items`, `business_categories`, `ranking_profiles`, `lot_business_profiles`, `regional_indicators`, `data_sources`, `sync_runs`, `users`, `audit_logs`, além das tabelas Laravel de filas/cache/sessões.

Migration principal: `database/migrations/2026_09_03_150000_create_terracap_domain.php`.

No PostgreSQL, ela cria a extensão PostGIS, geometrias originais/simplificadas de RAs, ponto e polígono opcional de lote, e índices espaciais GiST. Verificar permissões e suporte antes de executar. O localizador atual usa **`ST_Covers`**, incluindo bordas; não afirmar que implementa exatamente `ST_Contains` do plano original.

O volume de banco atualmente monta `/var/lib/postgresql` na imagem PostgreSQL 18. Confirme a convenção da imagem usada antes de alterar; não aplique automaticamente o caminho antigo de outras versões.

Na primeira instalação, depois da imagem/configuração aprovadas:

1. Subir somente o necessário para inicializar o banco e esperar disponibilidade real.
2. Confirmar alvo do banco sem imprimir senha e verificar que não é nenhum banco do CAMP.
3. Executar migrations de produção de forma controlada.
4. Executar `DatabaseSeeder` **uma única vez para provisionar a demonstração**, com minha aprovação.
5. Verificar contagens, vínculos, usuário administrador e consultas espaciais.
6. Preparar caches/permissões e só então iniciar app, trabalhadores e agendador conforme dependências reais.

**Atenção:** `DatabaseSeeder` usa `updateOrCreate`. Reexecutá-lo pode sobrescrever dados editados no administrativo e redefinir a senha do administrador a partir do ambiente. Não adicioná-lo automaticamente ao deploy de cada versão.

Verificações esperadas no PostgreSQL:

- Extensão PostGIS acessível e versão reportada.
- Exatamente 35 RAs, geometrias não vazias, válidas e SRID 4326.
- Índices GiST presentes nas colunas esperadas.
- Dez lotes publicados e exatamente três habilitados para busca na carga inicial.
- Localizações não nulas e compatíveis com a RA cadastrada por consulta espacial.
- Recomendação reproduzível para as três categorias e resposta adequada para consulta desconhecida.
- IDs de lote usados nos testes obtidos da API/banco, não presumidos.

Se uma geometria ou consulta falhar, diagnosticar e versionar a correção. Não substituir limites oficiais por polígonos inventados para a demonstração passar.

## 11. Etapas que você deve conduzir comigo

### Etapa A — inventário somente leitura

Comece com poucas perguntas e um bloco pequeno de diagnóstico. Levante progressivamente:

- Domínio desejado, acesso ao DNS, conexão SSH e sudo.
- SO, arquitetura, CPU, RAM disponível, swap, uso de disco/inodes e carga.
- Portas em uso e serviços do CAMP; referência de disponibilidade HTTP/HTTPS antes de alterações.
- Versão/presença de Docker e Compose, redes/contêineres existentes, sem listar ambientes secretos.
- Proxy ativo, organização dos sites e forma de renovação TLS; ler apenas o necessário, sem exportar configurações inteiras com segredos.
- Firewall e estratégia de backup existentes, sem redefini-los.

Ao terminar, apresente um quadro com fatos confirmados, pendências, riscos e arquitetura proposta. Peça aprovação para a instalação antes de alterações.

### Etapa B — preparação isolada e correções versionadas

- Definir diretório exclusivo, nome fixo de projeto Compose, porta privada e domínio.
- Revisar Dockerfile, Compose, backup, healthcheck, proxies e fila conforme as pendências acima.
- Fornecer patches/arquivos completos e instruções claras de onde salvar; não editar CAMP.
- Validar alterações, inspecionar diff, garantir que nenhum segredo entrou no Git e versionar no repositório Terracap.
- Preparar imagem pelo SHA selecionado, verificar runtime e assets.
- Só instalar Docker no host após avaliar impacto e obter aprovação específica, seguindo a documentação da distribuição. Não executar instalador remoto genérico sem revisão.

### Etapa C — inicialização privada

- Criar segredos, volumes, banco, migrations e carga inicial aprovada.
- Disponibilizar app apenas no acesso privado definido.
- Confirmar `/up`, endpoints, arquivos estáticos, mapa e login usando túnel/teste adequado antes de alterar DNS público.
- Confirmar recursos disponíveis e que o CAMP continua respondendo.

### Etapa D — domínio, proxy e HTTPS

- Confirmar DNS A/AAAA e possíveis proxies intermediários; não criar IPv6 incorreto.
- Configurar somente um site novo do Terracap no proxy existente.
- Salvar cópia da configuração pertinente, validar sintaxe e fazer recarga controlada, sem parar o serviço.
- Emitir certificado pelo mecanismo já usado no host, após confirmação de DNS e alcance do desafio; não disputar TLS com o Caddy interno.
- Conferir redirecionamento HTTPS, hostname, links gerados, cabeçalhos encaminhados, sessão/CSRF e ausência de conteúdo misto.
- Revalidar CAMP imediatamente; se regredir, restaurar somente a alteração que causou a regressão e reportar.

### Etapa E — persistência, operação e recuperação

- Verificar reinício/recriação dos serviços Terracap sem perda dos dados/documentos.
- Validar fila e uma única instância do agendador, saúde, rotação de logs e limites de recursos.
- Corrigir/testar rotina de backup, restaurar em destino isolado e combinar cópia externa.
- Registrar responsáveis, localização dos arquivos e rotina de atualização.
- Não reiniciar a VPS inteira como teste sem janela e autorização; comprovar políticas de reinício de forma menos invasiva primeiro.

### Etapa F — aceite funcional e dos dois sistemas

Verifique comigo:

- Terracap `/up` e APIs funcionando com PostgreSQL real.
- Mapa com contornos das 35 RAs e dez marcadores; worker carregado sem erro.
- Clique/isolamento de RA, retorno ao DF, três buscas e consulta desconhecida.
- Fichas dos sete lotes manuais e dos três pesquisáveis.
- Desktop/projetor e celular com painel correto, lista textual acessível.
- `/admin` protegido; login novo funciona sem erro de cookie, CSRF ou Livewire.
- Cadastro controlado de lote/edital, validação de publicação e aparição no mapa; combinar previamente limpeza/retirada desse registro de teste.
- Seletor geográfico do administrativo e upload de PDF permitido/validado; persistência após recriar app.
- Fonte simulada com sucesso/falha esperados, sem chamadas reais não autorizadas.
- Ausência de `.env`, logs ou arquivos privados acessíveis pela web.
- CAMP mantém página, saúde e serviços que estavam ativos antes; sem executar operações reais de negócio ou enviar mensagens para testar.
- Backup restaurável e retorno para imagem anterior documentado.

Não declare concluído o que ainda depender de teste manual. Liste pendências concretas.

## 12. Atualizações e retorno de versão

Quero um procedimento exclusivo do Terracap:

- Preparar imagem identificada pelo código, testar e preservar a imagem anterior.
- Manter persistentes banco, documentos, segredos e identidade dos volumes.
- Fazer backup antes de migrations; avaliar compatibilidade retroativa das alterações de schema.
- Migrar e ativar de forma controlada, renovar caches e atualizar fila/agendador para a mesma versão.
- Verificar saúde e ambos os sites após ativação.
- Em falha da aplicação, voltar à imagem anterior **apenas quando compatível com o banco atual**. Não desfazer dados destrutivamente nem restaurar backup por cima de novas gravações sem diagnóstico, decisão explícita e avaliação de perda.
- Não prometer zero indisponibilidade do Terracap com um único contêiner. Explicar eventual janela breve; CAMP não faz parte dessa janela.
- Nunca usar limpezas globais do Docker para remover imagens antigas: identificar somente recursos Terracap e preservar a versão de retorno.

## 13. Continuidade e entregáveis

A cada decisão/ação validada, mantenha um registro Markdown para eu salvar em `DEPLOY_CONTEXT.md` e atualize o resumo pertinente em `PROJECT_CONTEXT.md`. Se você não puder editar meus arquivos, forneça a seção pronta e diga explicitamente que eu preciso salvá-la.

Registrar, sem segredos:

- Data, etapa, decisão e evidência sanitizada.
- Servidor/ambiente identificado, domínio e porta escolhidos.
- Caminhos, projeto Compose, nomes dos serviços e volumes.
- SHA/tag/digest implantados e imagem anterior.
- Migrações e carga inicial realizadas; não repetir seed por engano.
- Resultado dos testes, estado do CAMP antes/depois, backups e restauração.
- Próximo passo e como voltar atrás na alteração mais recente.

Ao final, entregar um resumo operacional com URLs pública/admin, onde estão configurações e backups, como consultar logs, atualizar, recuperar e quais pendências restam. Nunca incluir senhas nesse resumo.

## 14. Arquivos de apoio que você deve pedir quando precisar

O prompt descreve um snapshot. Os arquivos anexados/commit escolhido são a referência concreta para comandos e patches:

1. `PROJECT_CONTEXT.md` e este prompt.
2. `Dockerfile`, `compose.yaml`, `.dockerignore` e `.env.production.example` — **somente o exemplo sem segredos**.
3. `docker/Caddyfile` e `docker/backup.sh`.
4. `composer.json`, `composer.lock`, `package.json`, `package-lock.json` e `vite.config.js`.
5. `bootstrap/app.php`, `app/Providers/AppServiceProvider.php`, `app/Providers/Filament/AdminPanelProvider.php` e arquivos de configuração específicos necessários.
6. Migration de domínio, `database/seeders/DatabaseSeeder.php`, `app/Services/RegionLocator.php` e GeoJSON, se necessário para reproduzir validação espacial.
7. `resources/js/map-engine.js`, código de mapa público/administrativo e testes, se houver falha visual.

Prefira ler pelo repositório quando acessível; caso contrário peça os anexos pertinentes em pequenos grupos. Não pedir a pasta inteira contendo `.env`, banco local, `vendor` ou arquivos pessoais.

## 15. Como começar sua primeira resposta

Confirme em poucas linhas que entendeu: **Terracap separado, CAMP preservado e Compose atual não pode ser usado inalterado por causa das portas públicas**.

Pergunte qual domínio/subdomínio quero usar e se consigo conectar por `ssh campconecta` com sudo. Entregue somente o primeiro bloco curto de diagnóstico, claramente identificado como PowerShell local ou Linux via SSH. Aguarde a minha saída antes de instalar, editar ou iniciar qualquer serviço.

Não responda com uma receita genérica completa para eu executar às cegas. Use todo este contexto para conduzir a implantação comigo, etapa por etapa, até o aceite real.
