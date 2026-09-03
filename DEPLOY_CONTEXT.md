# Terracap Conecta — contexto de implantação

Atualizado em: 2026-09-03

## Objetivo deste arquivo

Registrar, sem segredos, decisões, alterações, evidências e pontos de retorno da implantação do Terracap Conecta na VPS que já hospeda o CAMP Conecta.

Este arquivo deve ser atualizado após cada mudança relevante e antes de avançar para uma etapa que dependa do estado anterior.

## Regras operacionais confirmadas

- Preservar integralmente o CAMP Conecta.
- Executar diagnóstico antes de mudança.
- Fazer alterações pequenas, reversíveis e verificáveis.
- Não executar o `compose.yaml` original do Terracap na VPS compartilhada.
- Não publicar portas 80/443 diretamente por Docker.
- Não expor PostgreSQL no host.
- Não reutilizar banco, `.env`, APP_KEY, cookies, diretórios, volumes ou serviços do CAMP.
- Não usar `docker compose down -v`, limpezas globais do Docker, `migrate:fresh`, `db:wipe` ou operações destrutivas equivalentes.
- Não executar `DatabaseSeeder` automaticamente em deploys futuros.
- Não alterar Nginx/Certbot do CAMP sem backup da configuração pertinente, validação de sintaxe e possibilidade de retorno.
- Não declarar implantação concluída apenas porque uma página responde; validar aplicação, mapa, admin, banco, persistência, fila, backup/restauração e o CAMP.

## Identificação da aplicação

- Projeto: Terracap Conecta.
- Repositório: `https://github.com/alsanches/terracapconecta.git`.
- Branch: `main`.
- Commit funcional de referência: `f5ee1891b19af0be77230f20781f72750ab7e4fa`.
- Mensagem do commit: `fix: inclui worker cartografico e testa mapa no navegador`.
- Estado local antes das alterações de infraestrutura:
  - `PROJECT_CONTEXT.md` modificado por documentação.
  - `PROMPT-DEPLOY-VPS-CHATGPT-WEB.md` novo e não rastreado.
  - Nenhum arquivo de infraestrutura alterado ainda.
- Hostname público inicial aprovado: `terracap-conecta.179-198-101-134.sslip.io`.
- URL pública planejada: `https://terracap-conecta.179-198-101-134.sslip.io`.
- Porta privada candidata: `127.0.0.1:8011`.

## Arquitetura aprovada

```text
Internet :80/:443
        |
        v
Nginx existente no host
        |
        +-- campconecta.tech ----------------> CAMP atual / PHP-FPM / MariaDB
        |
        `-- terracap-conecta...sslip.io -----> 127.0.0.1:8011
                                                  |
                                                  v
                                             Docker Terracap
                                                  |
                          +-----------------------+-------------------+
                          |                       |                   |
                       app/web                 queue          PostgreSQL/PostGIS
                                                                  |
                                                              volumes próprios
```

Regras da arquitetura:

- CAMP continua fora do Docker.
- Nginx e Certbot permanecem no host.
- Terracap não disputa 80/443.
- Publicação do app deve ser apenas no loopback do host.
- Banco Terracap fica somente em rede Docker privada.
- Scheduler não será iniciado na primeira implantação enquanto não houver tarefas agendadas reais.
- Todos os recursos Docker do Terracap terão nomes/volumes/rede próprios.
- Aplicar limites de recursos antes da publicação.

## Etapa A — inventário da VPS

### A.1 Identificação

Validado em 03/09/2026:

- Host: `srv1862758`.
- Usuário operacional: `debian`.
- SO: Debian GNU/Linux 13.6 (Trixie).
- Kernel observado: `6.12.95+deb13-amd64`.
- Arquitetura: `amd64`.
- IP público: `179.198.101.134`.

### A.2 Capacidade antes do upgrade

Estado observado inicialmente:

- 1 vCPU.
- 3.8 GiB de RAM.
- Sem swap.
- Aproximadamente 50 GB de disco, com cerca de 25 GB livres.
- CAMP em produção e funcional.
- Docker não instalado.

Conclusão: capacidade considerada apertada para CAMP + PostgreSQL/PostGIS + Terracap.

### A.3 Upgrade da VPS

Upgrade realizado pelo usuário em 03/09/2026.

Estado validado após reboot:

- 2 vCPU.
- 7.8 GiB de RAM.
- Sem swap.
- Aproximadamente 99 GB de disco.
- Aproximadamente 72 GB livres no filesystem raiz.
- Após estabilização, aproximadamente 6.7 GiB de RAM disponível.
- `systemctl --failed`: nenhum serviço com falha.

Serviços do CAMP confirmados ativos:

- `nginx`
- `php8.4-fpm`
- `mariadb`
- `camp-conecta-queue`
- `camp-conecta-telegram`
- `camp-conecta-report-queue`

Baseline HTTP confirmado:

- `https://campconecta.tech/` → HTTP 200.
- `http://campconecta.tech/` → redirecionamento 301 para HTTPS.

Decisão: capacidade aprovada para continuar a implantação com limites de recursos no Terracap.

### A.4 Portas e proxy

Validado:

- Nginx do host ocupa `0.0.0.0:80`, `[::]:80`, `0.0.0.0:443` e `[::]:443`.
- MariaDB do CAMP escuta em `127.0.0.1:3306`.
- Porta `8011` estava livre nas verificações realizadas.
- Nginx possui site próprio do CAMP em `/etc/nginx/sites-available/camp-conecta`.
- Configuração Nginx validada com sucesso por `nginx -t`.
- Certbot timer ativo e habilitado.
- CAMP usa PHP-FPM 8.4 via socket local.
- Nenhuma alteração Nginx foi realizada para o Terracap até o momento.

### A.5 Hostname inicial

Decisão aprovada:

`terracap-conecta.179-198-101-134.sslip.io`

Evidência:

- `getent ahostsv4` resolveu o hostname para `179.198.101.134`.

Domínio próprio não é necessário nesta fase.

## Ponto de retorno antes do Docker

Snapshot manual confirmado no hPanel da Hostinger:

- VPS: `srv1862758.hstgr.cloud`.
- Data/hora de criação: 03/09/2026 às 17:13.
- Expiração exibida: 04/09/2026.
- Tempo estimado de restauração exibido: 30 min.
- Snapshot criado antes da instalação do Docker.

Também existem backups automáticos anteriores no provedor.

Observação: o snapshot é um ponto de retorno temporário e não substitui backup próprio da aplicação/banco.

## Etapa B0 — instalação controlada do Docker

### B0.1 Preflight APT

Repositórios existentes antes da Docker:

- Debian Trixie.
- Trixie updates.
- Trixie backports.
- Trixie security.
- Repositório Monarx.

Nenhum pacote Docker/containerd/podman estava instalado.

Arquivos Docker ausentes antes da configuração:

- `/etc/apt/keyrings/docker.asc`
- `/etc/apt/sources.list.d/docker.sources`

Pré-requisitos `ca-certificates` e `curl` já estavam atualizados.

### B0.2 Repositório Docker

Criados:

- `/etc/apt/keyrings/docker.asc`
- `/etc/apt/sources.list.d/docker.sources`

Repositório configurado:

- `https://download.docker.com/linux/debian`
- suite `trixie`
- componente `stable`
- arquitetura `amd64`

`apt update` concluído sem erro.

### B0.3 Simulação da instalação

Simulação APT validada antes da alteração:

- 0 upgraded.
- 10 pacotes novos.
- 0 removidos.
- 7 pacotes não atualizados.
- Nenhum pacote de Nginx, PHP, MariaDB ou serviços do CAMP seria removido/substituído.

Pacotes principais previstos:

- `docker-ce`
- `docker-ce-cli`
- `containerd.io`
- `docker-buildx-plugin`
- `docker-compose-plugin`

Dependências adicionais incluíram:

- `nftables`
- `libnftables1`
- `pigz`
- `dbus-user-session`
- `docker-ce-rootless-extras`

### B0.4 Firewall antes da instalação

Validado antes do Docker:

- `iptables` 1.8.11-2 já instalado.
- `iptables v1.8.11 (nf_tables)`.
- Alternativa ativa: `/usr/sbin/iptables-nft`.
- `net.ipv4.ip_forward = 0`.
- CAMP retornando HTTP 200.
- Nginx único ocupante de 80/443.
- Porta 8011 livre.

### B0.5 Instalação

Instalação realizada via APT oficial:

- Docker Engine: `29.7.2`.
- Docker Compose: `v5.5.1`.
- containerd: `2.3.4`.
- Buildx: `0.37.0`.

Resultado:

- 0 pacotes atualizados.
- 10 pacotes novos.
- 0 pacotes removidos.
- `docker.service` habilitado.
- `docker.socket` habilitado.
- `containerd.service` habilitado.

### B0.6 Validação pós-instalação

Confirmado:

- `docker`: active/enabled.
- `containerd`: active/enabled.
- Nenhum contêiner criado.
- `net.ipv4.ip_forward` mudou de 0 para 1.
- Docker criou chains próprias no iptables/nftables.
- Política `FORWARD` observada como `DROP`.
- Nginx continuou como único processo nas portas 80/443.
- Porta 8011 permaneceu livre.
- Todos os serviços do CAMP continuaram ativos.
- `https://campconecta.tech/` continuou retornando HTTP 200.
- Memória disponível após instalação: aproximadamente 6.4 GiB.

### B0.7 Rede Docker

Endereçamento do host:

- `eth0`: `179.198.101.134/24`.
- rota default via `179.198.101.254`.

Bridge Docker padrão:

- subnet `172.17.0.0/16`.
- gateway `172.17.0.1`.

Conclusão:

- Não há sobreposição entre a rede física `179.198.101.0/24` e a bridge Docker `172.17.0.0/16`.

Configuração Docker observada:

- `overlayfs`.
- cgroup v2.
- driver de cgroup `systemd`.
- Docker root: `/var/lib/docker`.
- `/etc/docker/daemon.json` inexistente.
- Nenhuma customização global do daemon aplicada.

Decisão: não alterar configuração global do Docker neste momento.

## Estado atual

Situação no encerramento desta atualização:

- CAMP íntegro e respondendo HTTP 200.
- Docker instalado e validado.
- Nenhum contêiner Terracap criado.
- Nenhuma rede Docker específica do Terracap criada.
- Nenhum volume Terracap criado.
- Nenhuma migration PostgreSQL executada.
- Nenhum seeder executado em produção.
- Nenhuma configuração Nginx do Terracap criada.
- Nenhum certificado Terracap emitido.
- Nenhum `.env.production` real criado na VPS.
- Nenhum segredo registrado neste arquivo.
- `compose.yaml` original continua proibido para execução na VPS compartilhada.
- Infraestrutura do repositório ainda precisa ser corrigida/versionada antes do primeiro `docker compose`.

## Pendências bloqueadoras antes do primeiro contêiner Terracap

1. Criar `compose.production.yaml` completo e exclusivo.
2. Garantir publicação somente `127.0.0.1:8011:80`.
3. Manter PostgreSQL sem `ports`.
4. Definir nomes fixos de projeto/rede/volumes para persistência entre releases.
5. Definir limites de CPU/memória.
6. Remover/não iniciar scheduler enquanto não houver agenda real.
7. Definir `SERVER_NAME=:80`.
8. Definir `APP_URL` com hostname sslip.io aprovado.
9. Configurar trusted proxies de forma restrita.
10. Definir cookie de sessão exclusivo e seguro.
11. Corrigir `DB_QUEUE_RETRY_AFTER > 120`.
12. Revisar Dockerfile:
    - PHP 8.4 real;
    - extensões exigidas pelo lock;
    - `php.ini-production`;
    - Composer/autoload;
    - build de assets;
    - `public/storage`;
    - ausência de `.env`, SQLite, logs e `public/hot`.
13. Alterar healthcheck para `/up`.
14. Preferir logs em `stderr`.
15. Corrigir rotina de backup:
    - detectar falha;
    - arquivo temporário;
    - formato restaurável;
    - retenção somente após sucesso;
    - validação/restauração isolada.
16. Restringir segredos por serviço.
17. Não reutilizar credenciais do CAMP.
18. Validar build real e migrations PostgreSQL/PostGIS antes da publicação.
19. Executar `DatabaseSeeder` somente uma vez e com aprovação.
20. Confirmar novamente o CAMP após cada alteração relevante.

## Próximo passo

Atualizar e versionar os arquivos de documentação antes de alterar a infraestrutura do repositório.

Depois:

1. revisar `compose.production.yaml`;
2. revisar `.env.production.example`;
3. revisar `Dockerfile`;
4. revisar `docker/backup.sh`;
5. revisar healthcheck/fila/trusted proxies;
6. executar testes/diff;
7. versionar;
8. somente então preparar o primeiro build real.

## Rollback do último passo realizado

Última mudança estrutural do host: instalação do Docker Engine e dependências.

Rollback preferencial em caso de regressão grave não diagnosticável:

1. interromper novas alterações;
2. coletar evidências do erro;
3. avaliar remoção controlada dos pacotes Docker somente se apropriado;
4. se necessário e autorizado, restaurar o snapshot de 03/09/2026 17:13 pelo hPanel.

Não restaurar snapshot automaticamente: isso reverte o estado inteiro da VPS e pode perder alterações posteriores. Exige decisão explícita.
