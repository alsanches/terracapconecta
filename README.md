# Terracap Conecta

MVP demonstrativo de inteligência territorial: um mapa das 35 Regiões Administrativas do Distrito Federal que permite explorar lotes fictícios e encontrar oportunidades por tipo de negócio usando um ranking transparente.

> Este projeto é um protótipo para concurso de inovação. Lotes, editais, valores, indicadores e recomendações são fictícios e não constituem informação ou oferta oficial da Terracap.

## O que já funciona

- Mapa público responsivo com limites oficiais das 35 RAs fornecidos pelo IPEDF.
- Seleção e enquadramento de uma RA, atenuação das demais e retorno ao DF completo.
- Dez lotes demonstrativos com ficha, edital e valor fictícios.
- Três buscas explicáveis: bar/gastronomia, coworking e comércio/serviços essenciais.
- Ranking determinístico com cinco fatores e contribuição visível de cada um.
- Painel Filament protegido em `/admin` para lotes, editais, fontes e sincronizações.
- Formulário de lote com mapa, determinação espacial da RA e regras de publicação.
- Simulação de integrações sem depender de serviços externos.
- APIs públicas versionadas e ambiente Docker com PostGIS, fila, agendador e backup.

## Tecnologias

PHP 8.4, Laravel 13, Filament 5, Blade, Alpine.js, Tailwind CSS, MapLibre GL JS, PostgreSQL 18 e PostGIS 3.6.

## Execução local

Pré-requisitos: PHP 8.4 ou superior, Composer 2, Node.js 22 e extensão SQLite do PHP.

```bash
composer install
npm ci
cp .env.example .env
php artisan key:generate
```

Para criar o administrador local, preencha `ADMIN_EMAIL` e `ADMIN_PASSWORD` no `.env`. Em seguida:

```bash
php artisan migrate:fresh --seed
npm run build
php artisan serve
```

Abra `http://127.0.0.1:8000` e `http://127.0.0.1:8000/admin`.

## APIs

- `GET /api/v1/regions`
- `GET /api/v1/lots?region=taguatinga&category=bar-gastronomia`
- `GET /api/v1/lots/{id}`
- `GET /api/v1/recommendations?query=quero%20abrir%20um%20bar`
- `GET /up`

## Verificação

```bash
php artisan test
vendor/bin/pint --test
npm run build
```

Com o banco demonstrativo preparado e o Google Chrome instalado, execute também `npm run test:browser` para verificar carregamento cartográfico, seleção de RA, buscas e experiência móvel.

## Publicação com Docker

1. Copie `.env.production.example` para `.env.production`.
2. Gere `APP_KEY` com `php artisan key:generate --show` e use senhas fortes exclusivas.
3. Ajuste `APP_URL`, `SERVER_NAME`, administrador e banco.
4. Garanta que apenas 80, 443 e SSH estejam liberadas no servidor.
5. Execute:

```bash
docker compose --env-file .env.production build
docker compose --env-file .env.production run --rm app php artisan migrate --force
docker compose --env-file .env.production run --rm app php artisan db:seed --force
docker compose --env-file .env.production up -d
docker compose --env-file .env.production exec app php artisan optimize
docker compose --env-file .env.production exec app php artisan storage:link
curl --fail https://SEU_DOMINIO/up
```

Faça backup antes de toda migração posterior. O serviço `backup` gera um `pg_dump` diário e mantém sete dias. O rollback da aplicação deve reutilizar a tag anterior em `APP_IMAGE_TAG`, sem reverter o banco destrutivamente.

## Continuidade e decisões

O arquivo [`PROJECT_CONTEXT.md`](PROJECT_CONTEXT.md) registra decisões, estado validado, pendências e dados necessários para retomar o desenvolvimento em outro chat.
