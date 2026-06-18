#!/usr/bin/env bash
set -euo pipefail

COMPOSE="docker compose --env-file .env -f deployments/docker-compose.yml"

for i in {1..30}; do
  if $COMPOSE exec -T mysql mysql -h127.0.0.1 -ueducation -peducation_secret mineadmin_education -e "SELECT 1 AS ok;" | grep -q "1"; then
    break
  fi

  if [ "$i" -eq 30 ]; then
    echo "mysql service is not ready" >&2
    exit 1
  fi

  sleep 1
done

for i in {1..30}; do
  if $COMPOSE exec -T redis redis-cli ping | grep -q "PONG"; then
    break
  fi

  if [ "$i" -eq 30 ]; then
    echo "redis service is not ready" >&2
    exit 1
  fi

  sleep 1
done

echo "services ok"
