#!/usr/bin/env bash
set -euo pipefail

php bin/hyperf.php list >/tmp/mineadmin-backend-command-list.txt
grep -q "start" /tmp/mineadmin-backend-command-list.txt

mkdir -p runtime
php bin/hyperf.php start > runtime/f00-health.log 2>&1 &
SERVER_PID=$!

cleanup() {
  kill "$SERVER_PID" >/dev/null 2>&1 || true
}
trap cleanup EXIT

for i in {1..30}; do
  if curl -fsS http://127.0.0.1:9501/health | grep -q '"code":200'; then
    composer test -- --filter EnvironmentHealthTest
    echo "backend smoke ok"
    exit 0
  fi
  sleep 1
done

cat runtime/f00-health.log >&2
exit 1
