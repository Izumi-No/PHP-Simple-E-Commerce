set shell := ["bash", "-eu", "-o", "pipefail", "-c"]

# ── Infra ──────────────────────────────────────────────────────────

# Sobe todos os serviços (postgres, redis, backend, frontend)
dev: down
	docker compose up --build -d

# Sobe os serviços sem rebuild
up:
	docker compose up -d

# Para os serviços
down:
	docker compose down

# Logs em tempo real
logs:
	docker compose logs -f

# Psql interativo no postgres
db:
	docker compose exec postgres psql -U postgres

# ── Backend ─────────────────────────────────────────────────────────

# Instala dependências do backend
backend-install:
	cd backend && composer install

# Roda os testes do backend
backend-test:
	cd backend && ./vendor/bin/phpunit

# Aplica as migrations
backend-migrate:
	cd backend && ./vendor/bin/phinx migrate

# Reverte a última migration
backend-rollback:
	cd backend && ./vendor/bin/phinx rollback

# ── Frontend ─────────────────────────────────────────────────────────

# Instala dependências do frontend
frontend-install:
	cd frontend && pnpm install

# Dev server (Parcel, porta 1234)
frontend-dev:
	cd frontend && pnpm dev

# Build de produção
frontend-build:
	cd frontend && pnpm build

# Lint/format (biome)
frontend-lint:
	cd frontend && biome check .

# ── Utils ────────────────────────────────────────────────────────────

# Abre o shell do flake (nix develop)
shell:
	nix develop

# Lista os comandos disponíveis
help:
	@just --list