#!/usr/bin/env bash
set -euo pipefail

cd web

corepack enable
pnpm lint
pnpm build
test -f dist/index.html

echo "admin web smoke ok"
