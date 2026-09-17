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

## Como executar

1. Copie o arquivo de ambiente:

```bash
cp .env.example .env
```

2. Suba os containers:

```bash
docker compose up --build
```

3. Acesse:

- Frontend: `http://localhost:3000`
- Backend: `http://localhost:8080`

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
