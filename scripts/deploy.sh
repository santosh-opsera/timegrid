#!/usr/bin/env bash
#
# Timegrid deployment script.
#
# Performs a zero-downtime-style deploy using release directories:
#   releases/<timestamp>/  — each deployment
#   current -> releases/<latest>  — symlink to active release
#
# Usage:
#   ./scripts/deploy.sh
#   APP_VERSION=v1.2.3 ./scripts/deploy.sh
#
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"

DEPLOY_PATH="${DEPLOY_PATH:-${PROJECT_ROOT}}"
RELEASES_DIR="${DEPLOY_PATH}/releases"
SHARED_DIR="${DEPLOY_PATH}/shared"
CURRENT_LINK="${DEPLOY_PATH}/current"
TIMESTAMP="$(date +%Y%m%d_%H%M%S)"
RELEASE_DIR="${RELEASES_DIR}/${TIMESTAMP}"
APP_VERSION="${APP_VERSION:-${TIMESTAMP}}"
KEEP_RELEASES="${KEEP_RELEASES:-5}"
COMPOSE_FILES="${COMPOSE_FILES:--f docker-compose.yml -f docker-compose.prod.yml}"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] [deploy] $*"
}

error() {
    log "ERROR: $*"
    exit 1
}

cd "${PROJECT_ROOT}"

log "Starting deployment (version: ${APP_VERSION})"

# Ensure directory structure
mkdir -p "${RELEASES_DIR}" "${SHARED_DIR}/storage" "${SHARED_DIR}/bootstrap/cache"

# Pull latest code
log "Pulling latest changes..."
git fetch origin
git pull --ff-only origin "$(git rev-parse --abbrev-ref HEAD)"

# Build production image
log "Building Docker image..."
export APP_VERSION
docker compose ${COMPOSE_FILES} build --no-cache app

# Tag release in git (optional, non-fatal)
if [ "${TAG_RELEASE:-false}" = "true" ]; then
    git tag -a "release-${APP_VERSION}" -m "Release ${APP_VERSION}" || true
fi

# Run database migrations before switching traffic
log "Running database migrations..."
docker compose ${COMPOSE_FILES} run --rm app php artisan migrate --force --no-interaction

# Clear and rebuild caches
log "Rebuilding application caches..."
docker compose ${COMPOSE_FILES} run --rm app php artisan config:cache
docker compose ${COMPOSE_FILES} run --rm app php artisan route:cache
docker compose ${COMPOSE_FILES} run --rm app php artisan view:cache
docker compose ${COMPOSE_FILES} run --rm app php artisan event:cache 2>/dev/null || true

# Restart services with new image
log "Restarting services..."
docker compose ${COMPOSE_FILES} up -d --remove-orphans

# Health check
log "Waiting for health check..."
max_attempts=30
attempt=0
until curl -sf http://localhost/health >/dev/null 2>&1 \
    || curl -sf http://localhost:8080/health >/dev/null 2>&1; do
    attempt=$((attempt + 1))
    if [ "${attempt}" -ge "${max_attempts}" ]; then
        error "Health check failed after deployment. Consider running ./scripts/rollback.sh"
    fi
    sleep 2
done

log "Health check passed."

# Record release metadata
echo "${APP_VERSION}" > "${RELEASES_DIR}/${TIMESTAMP}/VERSION" 2>/dev/null || \
    echo "${APP_VERSION}" > "${SHARED_DIR}/VERSION"
echo "${TIMESTAMP}" > "${SHARED_DIR}/CURRENT_RELEASE"

# Prune old releases
log "Pruning old releases (keeping ${KEEP_RELEASES})..."
if [ -d "${RELEASES_DIR}" ]; then
    ls -1dt "${RELEASES_DIR}"/*/ 2>/dev/null | tail -n +$((KEEP_RELEASES + 1)) | xargs -r rm -rf
fi

# Prune dangling Docker images
docker image prune -f >/dev/null 2>&1 || true

log "Deployment complete: version ${APP_VERSION}"
