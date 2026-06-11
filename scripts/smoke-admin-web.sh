#!/usr/bin/env bash
set -euo pipefail

cd web

command -v pnpm >/dev/null 2>&1 || corepack enable
pnpm lint
pnpm build
test -f dist/index.html

echo "admin web smoke ok"
