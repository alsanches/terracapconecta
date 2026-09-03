#!/bin/sh
set -eu

backup_dir="/backups"
retention_days="${BACKUP_RETENTION_DAYS:-7}"
interval_seconds="${BACKUP_INTERVAL_SECONDS:-86400}"

mkdir -p "${backup_dir}"

while true; do
    timestamp="$(date -u +%Y%m%dT%H%M%SZ)"
    temporary="${backup_dir}/.terracap-conecta-${timestamp}.dump.tmp"
    final="${backup_dir}/terracap-conecta-${timestamp}.dump"

    rm -f "${temporary}"

    if PGPASSWORD="${POSTGRES_PASSWORD}" pg_dump \
        --host="${POSTGRES_HOST}" \
        --username="${POSTGRES_USER}" \
        --dbname="${POSTGRES_DB}" \
        --format=custom \
        --no-owner \
        --no-privileges \
        --file="${temporary}" \
        && pg_restore --list "${temporary}" >/dev/null 2>&1
    then
        mv "${temporary}" "${final}"

        find "${backup_dir}" \
            -type f \
            -name 'terracap-conecta-*.dump' \
            -mtime "+${retention_days}" \
            -delete
    else
        echo "Backup PostgreSQL falhou em ${timestamp}" >&2
        rm -f "${temporary}"
    fi

    sleep "${interval_seconds}"
done
