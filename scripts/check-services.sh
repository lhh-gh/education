#!/usr/bin/env bash
set -euo pipefail

COMPOSE="docker compose --env-file .env -f deployments/docker-compose.yml"

$COMPOSE exec -T mysql mysql -ueducation -peducation_secret mineadmin_education -e "SELECT 1 AS ok;" | grep -q "1"
$COMPOSE exec -T redis redis-cli ping | grep -q "PONG"

echo "services ok"
