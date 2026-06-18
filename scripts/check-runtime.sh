#!/usr/bin/env bash
set -euo pipefail

require_command() {
  command -v "$1" >/dev/null 2>&1 || {
    echo "missing command: $1" >&2
    exit 1
  }
}

require_command php
require_command composer
require_command docker
require_command git
require_command node
require_command corepack

php -r 'exit(version_compare(PHP_VERSION, "8.1.0", ">=") ? 0 : 1);' || {
  echo "PHP version must be >= 8.1" >&2
  exit 1
}

node -e 'const major = Number(process.versions.node.split(".")[0]); process.exit(major >= 20 ? 0 : 1)' || {
  echo "Node.js major version must be >= 20" >&2
  exit 1
}

php -m | grep -qi '^pdo_mysql$'
php -m | grep -qi '^redis$'
php -m | grep -qi '^swoole$'
php -m | grep -qi '^json$'
php -m | grep -qi '^openssl$'
php -m | grep -qi '^fileinfo$'

echo "runtime ok"
