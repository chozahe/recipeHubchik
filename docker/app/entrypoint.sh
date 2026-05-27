#!/usr/bin/env bash

set -e

LOG_DIR="${LOG_DIR:-/var/log/recipehub}"
mkdir -p "$LOG_DIR"

parse_database_url() {
    php -r '
        $parts = parse_url(getenv("DATABASE_URL") ?: "");
        if (!is_array($parts)) {
            exit(0);
        }

        $key = $argv[1];
        if ("dbname" === $key) {
            echo isset($parts["path"]) ? ltrim($parts["path"], "/") : "";
            exit(0);
        }

        echo $parts[$key] ?? "";
    ' "$1"
}

# Kubernetes automatically injects POSTGRES_PORT=tcp://... for a Service named
# "postgres". Do not use POSTGRES_* here for parsing connection details: the
# application DATABASE_URL is the single source of truth.
DB_HOST="${RECIPEHUB_DB_HOST:-$(parse_database_url host)}"
DB_PORT="${RECIPEHUB_DB_PORT:-$(parse_database_url port)}"
DB_USER="${RECIPEHUB_DB_USER:-$(parse_database_url user)}"
DB_NAME="${RECIPEHUB_DB_NAME:-$(parse_database_url dbname)}"

DB_HOST="${DB_HOST:-postgres}"
DB_PORT="${DB_PORT:-5432}"

pg_isready_args=(-h "$DB_HOST" -p "$DB_PORT")
if [ -n "$DB_USER" ]; then
    pg_isready_args+=(-U "$DB_USER")
fi
if [ -n "$DB_NAME" ]; then
    pg_isready_args+=(-d "$DB_NAME")
fi

echo "Waiting for PostgreSQL at ${DB_HOST}:${DB_PORT}..."
for attempt in $(seq 1 60); do
    if pg_isready "${pg_isready_args[@]}" >/dev/null 2>&1; then
        echo "PostgreSQL is ready."
        break
    fi

    if [ "$attempt" -eq 60 ]; then
        echo "PostgreSQL is not ready after ${attempt} attempts." >&2
        exit 1
    fi

    sleep 1
done

php bin/console doctrine:migrations:migrate --no-interaction

if [ "$#" -eq 0 ]; then
    set -- frankenphp run --config /etc/caddy/Caddyfile
fi

exec "$@"
