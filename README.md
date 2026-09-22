# PHP Simple E-Commerce

Projeto simples de e-commerce com backend em PHP e frontend em TypeScript.

## Tecnologias

- **Backend:** PHP 8, PostgreSQL, Redis, PHPUnit, Phinx
- **Frontend:** TypeScript, Alpine.js, Parcel, Tailwind CSS
- **Infra:** Docker Compose

## Estrutura

- `/backend` API de produtos (`/products`)
- `/frontend` interface web
- `compose.yml` orquestra os serviços

## Pré-requisitos

- Docker
- Docker Compose
- [Nix](https://nixos.org) + [direnv](https://direnv.net) (opcional, para o ambiente de dev)
- [just](https://just.systems) (opcional, usado na raiz)

## Como executar

1. Copie o arquivo de ambiente:

```bash
cp .env.example .env
```

2. Suba os containers:

```bash
just dev
# ou diretamente:
docker compose up --build
```

3. Acesse:

- Frontend: `http://localhost:3000`
- Backend: `http://localhost:8080`

## Gerenciamento do monorepo

O repo usa Nix + direnv + um `justfile` na raiz como ponto único de entrada.

- **Nix** (`flake.nix`): define três ambientes — `default` (raiz, com tudo para rodar `just`), `backend` (PHP + Composer) e `frontend` (Node + pnpm + Biome).
- **direnv**: cada diretório carrega seu ambiente automaticamente — `backend/.envrc` usa `flake ..#backend` e `frontend/.envrc` usa `flake ..#frontend`.
- **just**: receitas para infra e tarefas de cada app. Veja todas com `just help`.

Comandos principais:

```bash
just dev                 # sobe todos os serviços
just backend-test        # testes do backend (PHPUnit)
just backend-migrate     # aplica migrations (Phinx)
just frontend-dev        # dev server do frontend (Parcel, porta 1234)
just frontend-build      # build de produção do frontend
just frontend-lint       # Biome check
```

## Endpoints principais

- `GET /products`
- `GET /products/{id}`
- `POST /products`
- `PUT /products/{id}`
- `DELETE /products/{id}`

## Testes (backend)

```bash
cd backend
composer install
./vendor/bin/phpunit
```

ou via just:

```bash
just backend-install
just backend-test
```
