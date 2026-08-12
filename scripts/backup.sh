#!/usr/bin/env bash
#
# Timegrid database backup script with rotation.
#
# Usage:
#   ./scripts/backup.sh
#   BACKUP_DIR=/var/backups/timegrid RETENTION_DAYS=14 ./scripts/backup.sh
#
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"

# Configuration (override via environment)
BACKUP_DIR="${BACKUP_DIR:-${PROJECT_ROOT}/storage/backups}"
RETENTION_DAYS="${RETENTION_DAYS:-30}"
TIMESTAMP="$(date +%Y%m%d_%H%M%S)"
BACKUP_FILE="${BACKUP_DIR}/timegrid_${TIMESTAMP}.sql.gz"

# Load environment variables if .env exists
if [ -f "${PROJECT_ROOT}/.env" ]; then
    set -a
    # shellcheck disable=SC1091
    source <(grep -E '^(DB_HOST|DB_PORT|DB_DATABASE|DB_USERNAME|DB_PASSWORD)=' "${PROJECT_ROOT}/.env" | sed 's/\r$//')
    set +a
fi

DB_HOST="${DB_HOST:-localhost}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-timegrid}"
DB_USERNAME="${DB_USERNAME:-timegrid}"
DB_PASSWORD="${DB_PASSWORD:-}"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $*"
}

mkdir -p "${BACKUP_DIR}"

log "Starting backup of database '${DB_DATABASE}'..."

if command -v docker >/dev/null 2>&1 && docker compose ps mysql 2>/dev/null | grep -q "running"; then
    log "Using Docker Compose MySQL container"
    docker compose exec -T mysql mysqldump \
        -u"${DB_USERNAME}" \
        -p"${DB_PASSWORD}" \
        --single-transaction \
        --routines \
        --triggers \
        "${DB_DATABASE}" | gzip > "${BACKUP_FILE}"
elif command -v mysqldump >/dev/null 2>&1; then
    log "Using local mysqldump"
    mysqldump \
        -h"${DB_HOST}" \
        -P"${DB_PORT}" \
        -u"${DB_USERNAME}" \
        -p"${DB_PASSWORD}" \
        --single-transaction \
        --routines \
        --triggers \
        "${DB_DATABASE}" | gzip > "${BACKUP_FILE}"
else
    log "ERROR: mysqldump not found and MySQL container not running"
    exit 1
fi

BACKUP_SIZE="$(du -h "${BACKUP_FILE}" | cut -f1)"
log "Backup completed: ${BACKUP_FILE} (${BACKUP_SIZE})"

# Rotate old backups
log "Rotating backups older than ${RETENTION_DAYS} days..."
DELETED_COUNT=0
while IFS= read -r -d '' old_file; do
    rm -f "${old_file}"
    DELETED_COUNT=$((DELETED_COUNT + 1))
    log "Deleted old backup: ${old_file}"
done < <(find "${BACKUP_DIR}" -name 'timegrid_*.sql.gz' -type f -mtime "+${RETENTION_DAYS}" -print0)

log "Rotation complete. Removed ${DELETED_COUNT} backup(s)."
log "Current backups:"
ls -lh "${BACKUP_DIR}"/timegrid_*.sql.gz 2>/dev/null || log "  (none)"
