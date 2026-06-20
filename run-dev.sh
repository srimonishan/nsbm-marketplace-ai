#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
RUNTIME_DIR="$ROOT_DIR/.data"
DB_DIR="$RUNTIME_DIR/mariadb"
DB_SOCKET="$RUNTIME_DIR/mariadb.sock"
DB_PID_FILE="$RUNTIME_DIR/mariadb.pid"
DB_LOG="$RUNTIME_DIR/mariadb.log"
SESSION_DIR="$RUNTIME_DIR/php-sessions"
DB_MARKER="$RUNTIME_DIR/schema-installed"
MIGRATION_DUMP="$RUNTIME_DIR/initial-database.sql"

mkdir -p "$RUNTIME_DIR" "$SESSION_DIR"

if [[ ! -d "$DB_DIR/mysql" ]]; then
    echo "Initializing persistent development database..."
    mariadb-install-db \
        --datadir="$DB_DIR" \
        --auth-root-authentication-method=normal \
        --skip-test-db >/dev/null
fi

database_is_ready() {
    mysqladmin --no-defaults --socket="$DB_SOCKET" --user=root ping >/dev/null 2>&1
}

STARTED_DATABASE=0
if ! database_is_ready; then
    rm -f "$DB_SOCKET" "$DB_PID_FILE"
    mariadbd \
        --datadir="$DB_DIR" \
        --skip-networking \
        --socket="$DB_SOCKET" \
        --pid-file="$DB_PID_FILE" \
        --log-error="$DB_LOG" &
    DB_PROCESS_ID=$!
    STARTED_DATABASE=1

    for _ in {1..50}; do
        database_is_ready && break
        if ! kill -0 "$DB_PROCESS_ID" 2>/dev/null; then
            echo "MariaDB failed to start. See $DB_LOG" >&2
            exit 1
        fi
        sleep 0.2
    done

    if ! database_is_ready; then
        echo "MariaDB did not become ready. See $DB_LOG" >&2
        exit 1
    fi
fi

cleanup() {
    if [[ "$STARTED_DATABASE" == "1" ]] && database_is_ready; then
        mysqladmin --no-defaults --socket="$DB_SOCKET" --user=root shutdown >/dev/null 2>&1 || true
    fi
}
trap cleanup EXIT INT TERM

if [[ ! -f "$DB_MARKER" ]]; then
    if [[ -s "$MIGRATION_DUMP" ]]; then
        echo "Migrating the existing marketplace data..."
        mysql --no-defaults --socket="$DB_SOCKET" --user=root \
            -e "CREATE DATABASE IF NOT EXISTS nsbm_marketplace CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
        mysql --no-defaults --socket="$DB_SOCKET" --user=root nsbm_marketplace < "$MIGRATION_DUMP"
    else
        echo "Loading the initial marketplace data..."
        mysql --no-defaults --socket="$DB_SOCKET" --user=root < "$ROOT_DIR/sql/database.sql"
    fi
    mysql --no-defaults --socket="$DB_SOCKET" --user=root <<'SQL'
CREATE USER IF NOT EXISTS 'nsbm_user'@'localhost' IDENTIFIED BY 'nsbm_pass123';
GRANT ALL PRIVILEGES ON nsbm_marketplace.* TO 'nsbm_user'@'localhost';
FLUSH PRIVILEGES;
SQL
    touch "$DB_MARKER"
fi

export DB_HOST="localhost;unix_socket=$DB_SOCKET"
export DB_NAME="nsbm_marketplace"
export DB_USER="nsbm_user"
export DB_PASS="nsbm_pass123"
export APP_URL="${APP_URL:-http://127.0.0.1:8000}"

echo "GreenLink Market is running at $APP_URL"
php -d "session.save_path=$SESSION_DIR" -S 127.0.0.1:8000 -t "$ROOT_DIR"
