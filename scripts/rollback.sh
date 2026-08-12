#!/usr/bin/env bash
#
# Timegrid rollback script.
#
# Reverts to the previous Docker image / git revision and restarts services.
#
# Usage:
#   ./scripts/rollback.sh
#   ./scripts/rollback.sh v1.2.2
#
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"

DEPLOY_PATH="${DEPLOY_PATH:-${PROJECT_ROOT}}"
RELEASES_DIR="${DEPLOY_PATH}/releases"
SHARED_DIR="${DEPLOY_PATH}/shared"
COMPOSE_FILES="${COMPOSE_FILES:--f docker-compose.yml -f docker-compose.prod.yml}"
TARGET_VERSION="${1:-}"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] [rollback] $*"
}

error() {
    log "ERROR: $*"
    exit 1
}

cd "${PROJECT_ROOT}"

log "Starting rollback procedure"

# Determine target version
if [ -z "${TARGET_VERSION}" ]; then
    if [ -f "${SHARED_DIR}/PREVIOUS_RELEASE" ]; then
        TARGET_VERSION="$(cat "${SHARED_DIR}/PREVIOUS_RELEASE")"
        log "Rolling back to previous release: ${TARGET_VERSION}"
    elif [ -d "${RELEASES_DIR}" ]; then
        # Second-most-recent release directory
        TARGET_VERSION="$(ls -1dt "${RELEASES_DIR}"/*/ 2>/dev/null | sed -n '2p' | xargs basename 2>/dev/null || true)"
        if [ -z "${TARGET_VERSION}" ]; then
            error "No previous release found. Specify a version: ./scripts/rollback.sh <version>"
        fi
        log "Rolling back to release: ${TARGET_VERSION}"
    else
        error "No release history found. Specify a git tag or commit: ./scripts/rollback.sh <ref>"
    fi
fi

# Save current release as previous (for chained rollbacks)
if [ -f "${SHARED_DIR}/CURRENT_RELEASE" ]; then
    cp "${SHARED_DIR}/CURRENT_RELEASE" "${SHARED_DIR}/PREVIOUS_RELEASE"
fi

# Checkout target git revision if it looks like a git ref
if git rev-parse --verify "${TARGET_VERSION}" >/dev/null 2>&1; then
    log "Checking out git ref: ${TARGET_VERSION}"
    git checkout "${TARGET_VERSION}"
elif git rev-parse --verify "release-${TARGET_VERSION}" >/dev/null 2>&1; then
    log "Checking out git tag: release-${TARGET_VERSION}"
    git checkout "release-${TARGET_VERSION}"
fi

export APP_VERSION="${TARGET_VERSION}"

# Rebuild and restart with previous version
log "Rebuilding image for version ${APP_VERSION}..."
docker compose ${COMPOSE_FILES} build app

log "Restarting services..."
docker compose ${COMPOSE_FILES} up -d --remove-orphans

# Clear caches (use previous config)
log "Clearing caches..."
docker compose ${COMPOSE_FILES} run --rm app php artisan config:clear
docker compose ${COMPOSE_FILES} run --rm app php artisan route:clear
docker compose ${COMPOSE_FILES} run --rm app php artisan view:clear
docker compose ${COMPOSE_FILES} run --rm app php artisan cache:clear

# Rebuild caches from rolled-back code
docker compose ${COMPOSE_FILES} run --rm app php artisan config:cache
docker compose ${COMPOSE_FILES} run --rm app php artisan route:cache
docker compose ${COMPOSE_FILES} run --rm app php artisan view:cache

# Health check
log "Verifying health after rollback..."
max_attempts=30
attempt=0
until curl -sf http://localhost/health >/dev/null 2>&1 \
    || curl -sf http://localhost:8080/health >/dev/null 2>&1; do
    attempt=$((attempt + 1))
    if [ "${attempt}" -ge "${max_attempts}" ]; then
        error "Health check failed after rollback — manual intervention required"
    fi
    sleep 2
done

echo "${TARGET_VERSION}" > "${SHARED_DIR}/CURRENT_RELEASE"

log "Rollback complete. Active version: ${APP_VERSION}"
log "NOTE: Database migrations are NOT automatically reversed."
log "      If the failed deployment ran migrations, restore from backup:"
log "        ./scripts/backup.sh  (to create a fresh backup first)"
log "        gunzip -c storage/backups/timegrid_<timestamp>.sql.gz | mysql ..."
