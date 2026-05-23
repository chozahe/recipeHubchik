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

DB_HOST="${POSTGRES_HOST:-$(parse_database_url host)}"
DB_PORT="${POSTGRES_PORT:-$(parse_database_url port)}"
DB_USER="${POSTGRES_USER:-$(parse_database_url user)}"
DB_NAME="${POSTGRES_DB:-$(parse_database_url dbname)}"

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
    set -- php -S 0.0.0.0:8080 -t public
fi

exec "$@"
