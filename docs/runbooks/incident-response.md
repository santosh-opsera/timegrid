# Incident Response Runbook — Timegrid

This runbook defines standard procedures for detecting, responding to, and recovering from production incidents affecting the Timegrid appointment platform.

## Severity Levels

| Level | Description | Response Time | Examples |
|-------|-------------|---------------|----------|
| **SEV-1** | Complete outage or data loss risk | 15 minutes | App unreachable, database down, active data corruption |
| **SEV-2** | Major degradation | 30 minutes | Booking failures, auth broken, elevated error rate (>5%) |
| **SEV-3** | Partial impact | 2 hours | Single feature broken, slow responses, non-critical job failures |
| **SEV-4** | Minor issue | Next business day | UI glitches, non-blocking warnings |

## On-Call Responsibilities

1. Acknowledge the alert within the response time for the severity level.
2. Open an incident channel (Slack `#incidents` or equivalent).
3. Assign roles: **Incident Commander (IC)**, **Technical Lead**, **Communications**.
4. Document timeline and actions in the incident log.
5. Resolve or escalate; conduct post-incident review within 48 hours for SEV-1/SEV-2.

## Detection

### Automated Monitoring

- **Health endpoint:** `GET /health` — returns JSON with database, cache, and disk checks.
- **Docker healthchecks:** `app`, `nginx`, `mysql`, and `redis` services report status via `docker compose ps`.
- **Sentry:** Application exceptions (`SENTRY_LARAVEL_DSN`).
- **CI pipeline:** Failed builds on merge to `main` block deployment.

### Manual Checks

```bash
# Health status
curl -s http://localhost/health | jq .

# Container status
docker compose -f docker-compose.yml -f docker-compose.prod.yml ps

# Application logs
docker compose logs --tail=100 app nginx

# Database connectivity
docker compose exec app php artisan db:show
```

## Initial Triage (First 5 Minutes)

1. **Confirm impact** — Is the health endpoint returning `200`? Can users log in and book?
2. **Check recent changes** — Was there a deploy in the last hour? Review `git log` and CI runs.
3. **Inspect infrastructure** — CPU, memory, disk (`df -h`), container restarts.
4. **Set severity** — Use the table above; announce in the incident channel.
5. **Begin timeline** — Record timestamps for all actions.

## Common Incident Playbooks

### Application Unreachable (502/504)

**Symptoms:** Nginx returns 502 Bad Gateway or 504 Gateway Timeout.

**Steps:**

1. Check if `app` container is running: `docker compose ps app`
2. Inspect PHP-FPM logs: `docker compose logs app --tail=200`
3. Verify nginx → PHP-FPM connectivity: `docker compose exec nginx wget -qO- http://app:9000` (FPM doesn't speak HTTP — check logs instead)
4. Restart app: `docker compose restart app`
5. If unresolved, rollback: `./scripts/rollback.sh`

### Database Connection Failures

**Symptoms:** Health check reports `database: error`; 500 errors on all pages.

**Steps:**

1. Check MySQL container: `docker compose ps mysql`
2. Test connection: `docker compose exec mysql mysqladmin ping -u root -p`
3. Review MySQL logs: `docker compose logs mysql --tail=100`
4. Check disk space on MySQL volume: `docker compose exec mysql df -h /var/lib/mysql`
5. If disk full, prune old backups and expand volume.
6. Restart MySQL only if no active writes: `docker compose restart mysql`

### Redis / Cache Failures

**Symptoms:** Health check reports `cache: error`; sessions or queues failing.

**Steps:**

1. Check Redis: `docker compose exec redis redis-cli ping`
2. Restart Redis: `docker compose restart redis`
3. Temporarily fall back to file cache by setting `CACHE_STORE=file` and restarting app (requires config cache clear).

### Failed Deployment

**Symptoms:** Errors immediately after running `./scripts/deploy.sh`.

**Steps:**

1. Do **not** run migrations again.
2. Roll back immediately: `./scripts/rollback.sh`
3. Verify health: `curl -s http://localhost/health | jq .status`
4. If rollback fails, restore database from latest backup (see below).

### Disk Space Exhausted

**Symptoms:** Health check reports `disk: warning`; write failures in logs.

**Steps:**

1. Check usage: `df -h` and review health endpoint disk check.
2. Clear Laravel caches: `docker compose exec app php artisan cache:clear`
3. Rotate logs: truncate or archive `storage/logs/*.log`
4. Prune Docker: `docker system prune -f`
5. Review backup rotation in `./scripts/backup.sh` (`RETENTION_DAYS`).

## Database Recovery

### Restore from Backup

```bash
# List available backups
ls -lh storage/backups/

# Restore (adjust credentials and filename)
gunzip -c storage/backups/timegrid_YYYYMMDD_HHMMSS.sql.gz | \
  docker compose exec -T mysql mysql -u timegrid -p timegrid
```

### Create Emergency Backup Before Recovery

```bash
./scripts/backup.sh
```

## Rollback Procedure

```bash
# Roll back to previous release automatically
./scripts/rollback.sh

# Roll back to specific version
./scripts/rollback.sh v1.2.2
```

**Important:** Rollback does **not** reverse database migrations. If a migration caused the incident, restore from backup or run manual down migrations.

## Communication Templates

### Internal (SEV-1/SEV-2)

> **Incident:** [Brief description]
> **Severity:** SEV-[1-4]
> **Status:** Investigating | Identified | Mitigating | Resolved
> **Impact:** [User-facing impact]
> **IC:** [Name]
> **Next update:** [Time]

### External (Customer-Facing)

> We are aware of an issue affecting [feature]. Our team is actively working on a resolution. We will provide an update within [timeframe]. We apologize for the inconvenience.

Do **not** share internal details (stack traces, hostnames, credentials).

## Escalation Path

1. **On-call engineer** — First responder
2. **Engineering lead** — SEV-1/SEV-2 after 30 minutes unresolved
3. **Infrastructure / DBA** — Database or infrastructure failures
4. **Security team** — Suspected breach, data exposure, or credential leak

## Post-Incident Review

Within 48 hours of resolving SEV-1 or SEV-2:

1. Write incident summary: timeline, root cause, impact duration, users affected.
2. Identify contributing factors and action items (with owners and due dates).
3. Update this runbook if new failure modes were discovered.
4. Store the review in the team wiki or `docs/runbooks/postmortems/`.

## Security Incidents

If a security incident is suspected (unauthorized access, credential leak, suspicious traffic):

1. **Do not destroy evidence** — Preserve logs before restarting services.
2. Rotate compromised credentials immediately (`APP_KEY`, DB passwords, API keys).
3. Block suspicious IPs at the nginx/firewall level.
4. Escalate to the security team.
5. Run `composer audit` and review recent deployments for supply-chain issues.

## Useful Commands Reference

```bash
# Full stack restart
docker compose -f docker-compose.yml -f docker-compose.prod.yml restart

# View real-time logs
docker compose logs -f app nginx mysql redis

# Run artisan commands
docker compose exec app php artisan <command>

# Deploy
./scripts/deploy.sh

# Backup
./scripts/backup.sh

# Security audit
composer audit
```

## Contacts

| Role | Contact |
|------|---------|
| On-call rotation | [PagerDuty / Opsgenie link] |
| Engineering lead | [email] |
| Infrastructure | [email] |
| Security | [email] |

*Last updated: 2026-08-12*
