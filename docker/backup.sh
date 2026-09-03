#!/bin/sh
set -eu

mkdir -p /backups

while true; do
    timestamp="$(date -u +%Y%m%dT%H%M%SZ)"
    filename="/backups/terracap-conecta-${timestamp}.sql.gz"

    PGPASSWORD="${POSTGRES_PASSWORD}" pg_dump \
        --host="${POSTGRES_HOST}" \
        --username="${POSTGRES_USER}" \
        --dbname="${POSTGRES_DB}" \
        --format=plain \
        --no-owner \
        --no-privileges | gzip > "${filename}"

    find /backups -type f -name 'terracap-conecta-*.sql.gz' -mtime +7 -delete
    sleep 86400
done
