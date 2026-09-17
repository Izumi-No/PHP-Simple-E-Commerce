FROM ghcr.io/pnpm/pnpm:12 AS builder

# Instala a versão do Node.js (ex: 22, 20, lts) globalmente
RUN pnpm runtime set node 22 -g

WORKDIR /app
COPY package.json pnpm-lock.yaml ./
RUN pnpm install --frozen-lockfile
COPY . .
RUN pnpm build

FROM nginx:alpine

WORKDIR /usr/share/nginx/html

COPY --from=builder /app/dist .
