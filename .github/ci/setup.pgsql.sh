#!/usr/bin/env bash

CURRENT_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

until docker exec postgres pg_isready; do
    echo "Waiting for PostgreSQL to start..."
    sleep 5
done

echo -e "Init PostgreSQL database..."
docker exec postgres psql -d postgres -U postgres -c "create database mineadmin"
docker exec postgres psql -d postgres -U postgres -tc "SELECT 1 FROM pg_database WHERE datname = 'mineadmin_test'" | grep -q 1 || docker exec postgres psql -d postgres -U postgres -c "create database mineadmin_test"
echo -e "Done\n"

wait
