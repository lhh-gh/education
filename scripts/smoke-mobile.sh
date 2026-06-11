#!/usr/bin/env bash
set -euo pipefail

cd mobile-uniapp

command -v pnpm >/dev/null 2>&1 || corepack enable
pnpm build:h5
test -f dist/build/h5/index.html

echo "mobile smoke ok"
