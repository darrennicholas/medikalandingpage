#!/usr/bin/env bash
set -euo pipefail

if [[ $# -ne 1 || ! -d "$1" ]]; then
    echo "Usage: $0 /path/to/backup-folder" >&2
    exit 2
fi

backup_dir="$(cd "$1" && pwd)"
(cd "$backup_dir" && sha256sum --check SHA256SUMS)
media_dir="$(mktemp -d)"
restore_database=""
cleanup() {
    if [[ -n "$restore_database" ]]; then
        MYSQL_PWD="${DB_PASSWORD:-}" mysql --host="${DB_HOST:-127.0.0.1}" --port="${DB_PORT:-3306}" --user="${DB_USERNAME:-root}" --execute="DROP DATABASE IF EXISTS \`$restore_database\`" >/dev/null || true
    fi
    rm -rf "$media_dir"
}
trap cleanup EXIT
tar -xzf "$backup_dir/media.tar.gz" -C "$media_dir"

if [[ -f "$backup_dir/database.sql" ]]; then
    restore_database="cms_restore_check_$(date -u +%Y%m%d%H%M%S)_$$"
    MYSQL_PWD="${DB_PASSWORD:-}" mysql --host="${DB_HOST:-127.0.0.1}" --port="${DB_PORT:-3306}" --user="${DB_USERNAME:-root}" --execute="CREATE DATABASE \`$restore_database\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
    MYSQL_PWD="${DB_PASSWORD:-}" mysql --host="${DB_HOST:-127.0.0.1}" --port="${DB_PORT:-3306}" --user="${DB_USERNAME:-root}" "$restore_database" < "$backup_dir/database.sql"
    table_count="$(MYSQL_PWD="${DB_PASSWORD:-}" mysql --host="${DB_HOST:-127.0.0.1}" --port="${DB_PORT:-3306}" --user="${DB_USERNAME:-root}" --batch --skip-column-names --execute="SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '$restore_database'")"
    [[ "$table_count" -gt 0 ]]
elif [[ -f "$backup_dir/database.sqlite" ]]; then
    sqlite_copy="$media_dir/restore-check.sqlite"
    cp "$backup_dir/database.sqlite" "$sqlite_copy"
    table_count="$(sqlite3 "$sqlite_copy" "SELECT COUNT(*) FROM sqlite_master WHERE type='table'")"
    [[ "$table_count" -gt 0 ]]
else
    echo "Backup database file is missing." >&2
    exit 1
fi

echo "Restore check passed: database tables and media archive are readable."
