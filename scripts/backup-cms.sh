#!/usr/bin/env bash
set -euo pipefail
umask 077

app_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
backup_root="${CMS_BACKUP_PATH:-$app_root/storage/app/private/backups}"
mkdir -p "$backup_root"
mkdir -p "$app_root/storage/app/public"
backup_root="$(cd "$backup_root" && pwd -P)"
public_root="$(cd "$app_root/storage/app/public" 2>/dev/null && pwd -P || true)"
if [[ -n "$public_root" && "$backup_root/" == "$public_root/"* ]]; then
    echo "CMS_BACKUP_PATH must be outside storage/app/public." >&2
    exit 2
fi
backup_dir="$backup_root/$(date -u +%Y%m%dT%H%M%SZ)"
mkdir -p "$backup_dir"
chmod 700 "$backup_dir"

case "${DB_CONNECTION:-sqlite}" in
    mysql)
        : "${DB_DATABASE:?DB_DATABASE must be set}"
        MYSQL_PWD="${DB_PASSWORD:-}" mysqldump \
            --single-transaction --quick --host="${DB_HOST:-127.0.0.1}" \
            --port="${DB_PORT:-3306}" --user="${DB_USERNAME:-root}" \
            "$DB_DATABASE" > "$backup_dir/database.sql"
        ;;
    sqlite)
        database_path="${DB_DATABASE:-database/database.sqlite}"
        [[ "$database_path" = /* ]] || database_path="$app_root/$database_path"
        sqlite_backup_path="$backup_dir/database.sqlite"
        if command -v cygpath >/dev/null 2>&1; then
            sqlite_backup_path="$(cygpath -m "$sqlite_backup_path")"
        fi
        sqlite_backup_path="${sqlite_backup_path//\'/\'\'}"
        sqlite3 "$database_path" ".backup '$sqlite_backup_path'"
        ;;
    *)
        echo "Unsupported DB_CONNECTION: ${DB_CONNECTION}" >&2
        exit 2
        ;;
esac

tar -czf "$backup_dir/media.tar.gz" -C "$app_root/storage/app/public" .
(cd "$backup_dir" && sha256sum database.* media.tar.gz > SHA256SUMS)
echo "CMS backup created: $backup_dir"
