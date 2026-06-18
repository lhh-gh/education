SHELL := /usr/bin/env bash

COMPOSE := docker compose --env-file .env -f deployments/docker-compose.yml

.PHONY: env runtime-check up down services-check backend-install backend-smoke admin-install admin-build mobile-install mobile-build smoke

env:
	test -f .env || cp .env.example .env

runtime-check: env
	bash scripts/check-runtime.sh

up: env
	$(COMPOSE) up -d

down: env
	$(COMPOSE) down

services-check: env
	bash scripts/check-services.sh

backend-install: env
	composer install

backend-smoke: env
	bash scripts/smoke-backend.sh

admin-install: env
	cd web && (command -v pnpm >/dev/null 2>&1 || corepack enable) && pnpm install --frozen-lockfile

admin-build: env
	bash scripts/smoke-admin-web.sh

mobile-install: env
	test -f mobile-uniapp/package.json || npx degit dcloudio/uni-preset-vue#vite-ts mobile-uniapp
	cd mobile-uniapp && (command -v pnpm >/dev/null 2>&1 || corepack enable) && pnpm install

mobile-build: env
	bash scripts/smoke-mobile.sh

smoke: runtime-check up services-check backend-smoke admin-build mobile-build
