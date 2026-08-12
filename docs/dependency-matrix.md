# Timegrid Dependency Compatibility Matrix

**Project:** Timegrid — Laravel 5.3 appointment booking platform  
**Target stack:** Laravel 12 + PHP 8.4  
**Document version:** 1.0  
**Last updated:** 2026-08-12

---

## Executive Summary

Timegrid currently runs on **Laravel 5.3** and **PHP ≥5.6** with **37 production** and **13 dev** Composer packages, plus **25 Bower** front-end dependencies. Roughly **60% of production packages** are incompatible with Laravel 12 / PHP 8.4 without replacement, major version upgrades, or removal.

| Category | Count | Outcome |
|----------|------:|---------|
| Upgrade in place | 14 | Bump to current major; minimal code change |
| Replace | 8 | Swap for maintained alternative |
| Remove | 9 | Dead weight or superseded by Laravel core |
| Fork / maintain internally | 2 | `concierge` (done), `plan-config` (TBD) |
| Front-end (Bower) | 25 | Remove Bower; migrate to Vite + npm |

**Highest-risk items:** five `dev-master` Composer pins, Intervention Image v2 + imagecache coupling, Notifynder notification subsystem, Beautymail templating, and the Bootstrap 3 / jQuery front-end stack.

**Key decisions (locked):**

| Current | Action | Replacement |
|---------|--------|-------------|
| `timegridio/concierge` | Fork | Internal fork (done) |
| `fenos/notifynder` | Replace | Laravel Notifications |
| `jenssegers/rollbar` | Replace | `sentry/sentry-laravel` |
| `snowfire/beautymail` | Replace | Laravel Mailables + Blade |
| `timegridio/icalreader` | Replace | `sabre/vobject` |
| `laravelcollective/html` | Remove | Native Blade / Livewire |
| `patricktalmadge/bootstrapper` | Remove | Tailwind / Bootstrap 5 via Vite |
| `ipunkt/laravel-analytics` | Remove | — (optional GA4 snippet) |
| `alariva/tidiochat` | Remove | — (optional JS widget) |
| Bower packages | Remove | npm + Vite |
| `laravel/framework` | Upgrade | `^12.0` |

---

## Compatibility Matrix

Legend for compatibility columns:

| Symbol | Meaning |
|--------|---------|
| ✅ | Compatible / supported on target stack |
| ⚠️ | Partial support, fork required, or significant refactor |
| ❌ | Incompatible / abandoned |
| ➖ | N/A (removed or bundled with framework) |

### Production Dependencies (37)

| Package | Current | Latest | PHP 8.4 | Laravel 12 | Action | Replacement | Notes |
|---------|---------|--------|---------|------------|--------|-------------|-------|
| `php` | `>=5.6` | 8.4.x | ✅ | ✅ | **Upgrade** | `^8.4` | Laravel 12 requires PHP ≥8.2; target 8.4 for longevity |
| `laravel/framework` | `5.3.*` | `12.x` | ✅ | ✅ | **Upgrade** | `^12.0` | Full app rewrite of bootstrap, middleware, routes, Eloquent patterns |
| `nesbot/carbon` | `^1.22` | `3.x` | ✅ | ✅ | **Upgrade** | `^3.0` | Bundled with Laravel 12; remove explicit require unless pinned |
| `guzzlehttp/guzzle` | `~6.0` | `7.9.x` | ✅ | ✅ | **Upgrade** | `^7.8` | Laravel 12 pulls Guzzle 7 transitively; drop direct require if unused |
| `anhskohbo/no-captcha` | `2.*` | `3.6.x` | ✅ | ✅ | **Upgrade** | `^3.6` | v3 supports Laravel 10–12; config key rename |
| `barryvdh/laravel-snappy` | `~0.2` | `1.0.x` | ✅ | ⚠️ | **Upgrade** | `^1.0` | Requires `wkhtmltopdf` binary on server; consider `barryvdh/laravel-dompdf` |
| `bassjobsen/bootstrap-3-typeahead` | `~4.0` | — | ❌ | ❌ | **Remove** | npm `@coreui/coreui` or Alpine/HTMX autocomplete | PHP wrapper for deprecated Twitter typeahead; tied to BS3 |
| `creativeorange/gravatar` | `~1.0` | `1.0.24` | ✅ | ⚠️ | **Upgrade / Replace** | `^1.0` or inline helper | Last tagged 2020; low maintenance — 10-line helper is viable |
| `fenos/notifynder` | `^3.0` | — (abandoned) | ❌ | ❌ | **Replace** | Laravel Notifications | Custom notification classes + `database`/`mail` channels |
| `graham-campbell/markdown` | `^6.0` | `16.x` | ✅ | ✅ | **Upgrade** | `^16.0` | Major jump; follows Laravel version mapping on package page |
| `intervention/image` | `^2.2` | `3.11.x` | ⚠️ | ⚠️ | **Upgrade** | `^3.0` | v3 is PHP 8+ native; API breaking (GD/Imagick drivers renamed) |
| `intervention/imagecache` | `^2.2` | — (abandoned) | ❌ | ❌ | **Remove** | Laravel cache + Intervention v3 or Glide | No Laravel 12 support; roll own cached image route |
| `ipunkt/laravel-analytics` | `~1.1` | — (abandoned) | ❌ | ❌ | **Remove** | Optional GA4 `<script>` in layout | Universal Analytics deprecated; package unmaintained since 2017 |
| `laracasts/flash` | `~2.0` | `3.2.x` | ✅ | ⚠️ | **Upgrade / Remove** | `^3.2` or `session()->flash()` | v3 supports Laravel 8–11; verify L12 before commit — native flash may suffice |
| `laravel/socialite` | `~2.0` | `5.24.x` | ✅ | ✅ | **Upgrade** | `^5.15` | OAuth provider APIs changed; update callback controllers |
| `laravelcollective/html` | `~5.2` | — (abandoned) | ❌ | ❌ | **Remove** | Native Blade forms | Collective HTML officially abandoned; no L6+ support |
| `mccool/laravel-auto-presenter` | `^4.0` | — (abandoned) | ❌ | ❌ | **Replace** | ViewModels / API Resources | Decorator pattern via Eloquent `$with` or dedicated DTO classes |
| `patricktalmadge/bootstrapper` | `~5` | — (abandoned) | ❌ | ❌ | **Remove** | Tailwind 4 or Bootstrap 5 via Vite | BS3 form/html builder; last commit 2016 |
| `pid/speakingurl` | `^0.11.0` | `0.20.x` | ✅ | ✅ | **Upgrade** | `^0.20` | Standalone slugify lib; no Laravel coupling |
| `propaganistas/laravel-phone` | `~2.0` | `5.3.x` | ✅ | ✅ | **Upgrade** | `^5.3` | v5 uses `libphonenumber`; validation rule syntax changed |
| `sorich87/bootstrap-tour` | `^0.10.2` | — | ❌ | ❌ | **Remove** | `driver.js` or `shepherd.js` via npm | Composer asset package; BS3 tour library |
| `stevebauman/location` | `~2.0` | `7.6.x` | ✅ | ✅ | **Upgrade** | `^7.0` | IP geolocation; v7 supports Laravel 10–11, likely L12 |
| `twitter/typeahead.js` | `^0.11.1` | — (deprecated) | ➖ | ➖ | **Remove** | npm `@algolia/autocomplete-js` | Deprecated by Twitter; asset-only Composer package |
| `webpatser/laravel-countries` | `dev-master` | — (abandoned) | ❌ | ❌ | **Replace** | `league/iso3166` or `rinvex/countries` | No tagged releases; Laravel 5 era |
| `jenssegers/agent` | `^2.3` | `2.6.x` | ⚠️ | ⚠️ | **Replace** | `mobiledetect/mobiledetectlib` | Package abandoned 2020; may work on 8.4 but unmaintained |
| `laracasts/utilities` | `^2.1` | `3.2.x` | ✅ | ⚠️ | **Upgrade** | `^3.2` | JavaScript variable bridge; verify L12 compatibility |
| `alariva/tidiochat` | `^2.0` | — (abandoned) | ❌ | ❌ | **Remove** | Optional Tidio JS embed | Thin Blade directive wrapper; drop or inline script |
| `timegridio/concierge` | `dev-master#90e65c` | — | ⚠️ | ⚠️ | **Fork** | Internal `timegridio/concierge` fork | **Done** — modernize fork for L12 service provider + PSR-4 |
| `jenssegers/rollbar` | `^1.5` | — (abandoned) | ❌ | ❌ | **Replace** | `sentry/sentry-laravel ^4.0` | Rollbar Laravel SDK unmaintained; Sentry is Laravel-first |
| `eluceo/ical` | `^0.11` | `2.14.x` | ✅ | ✅ | **Upgrade** | `^2.14` | iCal generation; v2 namespace + PHP 8 types |
| `snowfire/beautymail` | `dev-master` | — (abandoned) | ❌ | ❌ | **Replace** | Laravel Mailables + Markdown mail | Themed email wrapper; Laravel native mail is sufficient |
| `timegridio/icalreader` | `dev-master` | — | ❌ | ❌ | **Replace** | `sabre/vobject ^4.5` | Parse/import ICS files; Sabre is industry standard |
| `spatie/laravel-cookie-consent` | `^1.2` | `3.3.x` | ✅ | ✅ | **Upgrade** | `^3.3` | v3 supports Laravel 10–11; publish new views |
| `torann/geoip` | `1.0.*` | `3.0.x` | ✅ | ✅ | **Upgrade** | `^3.0` | GeoIP facade; config structure changed in v2+ |
| `geoip2/geoip2` | `~2.1` | `3.2.x` | ✅ | ✅ | **Upgrade** | `^2.13` or `^3.0` | MaxMind reader; v3 requires PHP 8.1+ |
| `seanstewart/plan-config` | `dev-master` | — | ⚠️ | ⚠️ | **Fork / Inline** | Config file or internal package | Small plan-tier helper; fork or absorb into `config/plans.php` |
| `jackiedo/timezonelist` | `^5.0` | `5.1.x` | ✅ | ✅ | **Keep** | `^5.1` | Timezone dropdown generator; PHP 8 compatible |

### Dev Dependencies (13)

| Package | Current | Latest | PHP 8.4 | Laravel 12 | Action | Replacement | Notes |
|---------|---------|--------|---------|------------|--------|-------------|-------|
| `barryvdh/laravel-debugbar` | `^2.0` | `3.14.x` | ✅ | ✅ | **Upgrade** | `^3.14` | Dev-only; supports Laravel 11, verify L12 tag |
| `caouecs/laravel-lang` | `~3.0` | — (renamed) | ❌ | ❌ | **Replace** | `laravel-lang/common ^6.0` | Project renamed; new package structure |
| `codeclimate/php-test-reporter` | `dev-master` | — (deprecated) | ❌ | ❌ | **Remove** | Codecov / Coveralls GitHub Action | Code Climate reporter deprecated |
| `fzaninotto/faker` | `~1.0` | — (abandoned) | ❌ | ❌ | **Replace** | `fakerphp/faker ^1.24` | Fork maintained as `fakerphp/faker` |
| `laracasts/generators` | `^1.1` | — (abandoned) | ❌ | ❌ | **Remove** | `php artisan make:*` | Laravel core generators supersede this |
| `phpunit/phpunit` | `~5.0` | `11.5.x` | ✅ | ✅ | **Upgrade** | `^11.0` | PHPUnit 11 requires PHP 8.2+; test suite full rewrite |
| `phpunit/phpunit-selenium` | `~3.0` | — (abandoned) | ❌ | ❌ | **Remove** | `laravel/dusk ^8.0` or Pest + Playwright | Selenium bindings unmaintained |
| `potsky/laravel-localization-helpers` | `2.4.*` | — (abandoned) | ❌ | ❌ | **Remove** | `laravel-lang/publisher` or manual | lang:refresh command; low value in modern workflow |
| `symfony/css-selector` | `~3.0` | `7.2.x` | ✅ | ✅ | **Remove** | (bundled) | Provided by Laravel 12 / Symfony 7 |
| `symfony/dom-crawler` | `~3.0` | `7.2.x` | ✅ | ✅ | **Remove** | (bundled) | Only needed if writing crawler tests |
| `mockery/mockery` | `0.9.*` | `1.6.x` | ✅ | ✅ | **Upgrade** | `^1.6` | Required for PHPUnit mocking |
| `tightenco/mailthief` | `~0.3` | — (abandoned) | ❌ | ❌ | **Remove** | `Mail::fake()` | Laravel native mail fakes since 5.5 |

### Front-End Dependencies — Bower (25) → Remove

| Package | Current (Bower) | Action | Replacement (npm) | Notes |
|---------|-----------------|--------|-------------------|-------|
| `bootstrap` | `~3.3.7` | **Remove** | `bootstrap@5` or Tailwind 4 | BS3 EOL; migrate markup |
| `jquery` | `~2.2` | **Remove** | Alpine.js / vanilla JS | jQuery 2 EOL; security CVEs |
| `adminlte` | `^2.3.8` | **Remove** | Custom layout or Filament | AdminLTE 2 is BS3-only |
| `select2` | `^4.0.3` | **Replace** | `tom-select` or native `<select>` | |
| `select2-bootstrap-theme` | `^0.1.0-beta.9` | **Remove** | — | Tied to Select2 + BS3 |
| `fullcalendar` | `^3.0.1` | **Replace** | `@fullcalendar/core@6` | Agenda/calendar views |
| `eonasdan-bootstrap-datetimepicker` | `~4.17.37` | **Replace** | `flatpickr` | BS3 datetime picker abandoned |
| `bootstrap-datepicker` | `^1.6.4` | **Replace** | `flatpickr` | |
| `bootstrap-timepicker` | `^0.5.2` | **Replace** | `flatpickr` | |
| `air-datepicker` | `^2.2.3` | **Replace** | `flatpickr` | Consolidate on one date lib |
| `moment-timezone` | `^0.5.9` | **Replace** | `dayjs` + timezone plugin | Lighter bundle via Vite |
| `bootstrap-select` | `~1.9.3` | **Replace** | `tom-select` | |
| `bootstrap-switch` | `^3.3.2` | **Replace** | Tailwind toggle / BS5 switch | |
| `bootstrap-validator` | `~0.11` | **Remove** | Laravel Form Requests + JS validation | Server-side is authoritative |
| `bootstrap-tour` | `~0.10.2` | **Replace** | `driver.js` | Duplicates Composer `sorich87` pin |
| `tooltipster` | `~3.3.0` | **Replace** | Tippy.js (`@floating-ui/dom`) | |
| `animate.css` | `~3.5.0` | **Replace** | `animate.css@4` or Tailwind animate | |
| `jquery-ui` | `^1.12.1` | **Remove** | — | Sortable etc. via `@shopify/draggable` if needed |
| `jquery.steps` | `^1.1.0` | **Replace** | Livewire wizard or Alpine stepper | Booking wizard |
| `jquery-slugify` | `~1.2.3` | **Remove** | Server-side `pid/speakingurl` | Already have PHP slugify |
| `jquery-highlighttextarea` | `~3.1.1` | **Remove** | CSS `highlight` or CodeMirror | |
| `jquery-bootstrap-newsbox` | `~1.0.2` | **Remove** | — | News ticker — evaluate if still used |
| `mjolnic-bootstrap-colorpicker` | `~2.3.0` | **Replace** | `@simonwep/pickr` | Business branding color picker |
| `bootstrap-list-filter` | `^0.3.2` | **Remove** | Alpine filter or Livewire | |
| `clipboard` | `^1.5.15` | **Replace** | `clipboard@2` via npm | Copy-to-clipboard for sharing links |

---

## Risk Assessment: `dev-master` Packages

Unpinned `dev-master` dependencies are the highest structural risk in the current `composer.json`. None have stable releases compatible with modern Laravel.

| Package | Risk Level | Issue | Mitigation |
|---------|------------|-------|------------|
| `timegridio/concierge` | 🟡 Medium (was 🔴) | Core domain package on unversioned commit `#90e65c`; no PHP 8 types, L5 service provider | **Fork complete.** Tag semver releases; add CI matrix PHP 8.4 × Laravel 12 |
| `webpatser/laravel-countries` | 🔴 High | Abandoned; `dev-master` can change without notice; Laravel 5 facades | Replace with `rinvex/countries` or static JSON + `league/iso3166` |
| `snowfire/beautymail` | 🔴 High | Abandoned 2018; wraps Laravel 5 mail API; dev-master unreproducible | Migrate each template to `Mailable` + `@component` or Markdown mail |
| `timegridio/icalreader` | 🔴 High | Internal package, no tags; unknown PHP 8 behavior | Replace with `sabre/vobject`; write adapter for existing import code paths |
| `seanstewart/plan-config` | 🟡 Medium | Small surface area but no semver; billing/plan gating depends on it | Fork to `timegridio/plan-config` or flatten to `config/plans.php` + enum |
| `codeclimate/php-test-reporter` (dev) | 🟢 Low | Dev-only; deprecated upstream | Delete; use GitHub Actions coverage upload |

**Reproducibility rule going forward:** no `dev-master` in production `require`. All internal packages must carry semver tags and Packagist/VCS version constraints (e.g. `^1.0`).

---

## Action Plan

### Phase 0 — Foundation (Week 1–2)

- [ ] Bump PHP requirement to `^8.4` in Docker / CI / `composer.json`
- [ ] Create Laravel 12 skeleton branch; install `laravel/framework ^12.0`
- [ ] Replace dev toolchain: PHPUnit 11, Mockery 1.6, Debugbar 3.x, `fakerphp/faker`
- [ ] Remove `post-install-cmd` / `optimize` references (removed in modern Laravel)
- [ ] Delete `bower.json`, `.bowerrc`, `bower_components/`; scaffold Vite + npm

### Phase 1 — Drop Dead Weight (Week 2–3)

- [ ] Remove: `laravelcollective/html`, `bootstrapper`, `ipunkt/laravel-analytics`, `alariva/tidiochat`
- [ ] Remove: `intervention/imagecache`, `twitter/typeahead.js`, `bassjobsen/bootstrap-3-typeahead`, `sorich87/bootstrap-tour`
- [ ] Remove dev: `laracasts/generators`, `mailthief`, `phpunit-selenium`, `codeclimate/php-test-reporter`, `potsky/laravel-localization-helpers`
- [ ] Audit views/controllers for Collective/Bootstrapper helpers; convert to Blade

### Phase 2 — Replace Subsystems (Week 3–6)

- [ ] **Notifications:** Map Notifynder categories → Laravel Notification classes; migrate `notifications` table or recreate
- [ ] **Email:** Port Beautymail themes → `resources/views/mail/` Mailables; verify booking confirmation, reminder, cancellation flows
- [ ] **Error tracking:** Install `sentry/sentry-laravel ^4.0`; remove Rollbar config/env keys
- [ ] **iCal import:** Swap `icalreader` → `sabre/vobject`; keep `eluceo/ical ^2` for export
- [ ] **Countries:** Replace `webpatser/laravel-countries` → `rinvex/countries` or static ISO data
- [ ] **Presenters:** Replace `laravel-auto-presenter` with ViewModels per bounded context (Booking, Business, User)

### Phase 3 — Upgrade In Place (Week 4–7)

- [ ] `intervention/image ^3` — refactor resize/crop calls; implement cache route
- [ ] `propaganistas/laravel-phone ^5`, `stevebauman/location ^7`, `torann/geoip ^3`
- [ ] `laravel/socialite ^5`, `graham-campbell/markdown ^16`, `spatie/laravel-cookie-consent ^3`
- [ ] `anhskohbo/no-captcha ^3.6`, `barryvdh/laravel-snappy ^1.0` (or dompdf)
- [ ] `jenssegers/agent` → `mobiledetect/mobiledetectlib` (or Jaybizzle fork)
- [ ] Publish and tag **`timegridio/concierge` fork** `^2.0` with L12 service provider

### Phase 4 — Front-End Modernization (Week 5–10)

- [ ] Vite entrypoints for admin, booking wizard, public directory
- [ ] Replace BS3/jQuery plugin matrix (see Bower table) with npm equivalents
- [ ] Migrate AdminLTE 2 layouts to new admin shell (Tailwind or BS5)
- [ ] FullCalendar 6 for agenda; Flatpickr for all date/time inputs
- [ ] Remove asset publishing from Composer packages (`php artisan vendor:publish` audit)

### Phase 5 — Verification (Week 10–12)

- [ ] PHPUnit/Pest feature tests for booking flow, notifications, iCal round-trip
- [ ] Manual QA: booking wizard, owner dashboard, calendar sync, email templates
- [ ] Security scan (Composer audit, `roave/security-advisories`)
- [ ] Performance baseline: image handling, GeoIP lookups, PDF generation

---

## Target `composer.json` (Illustrative)

```json
{
    "require": {
        "php": "^8.4",
        "laravel/framework": "^12.0",
        "timegridio/concierge": "^2.0",
        "anhskohbo/no-captcha": "^3.6",
        "barryvdh/laravel-snappy": "^1.0",
        "graham-campbell/markdown": "^16.0",
        "intervention/image": "^3.0",
        "laracasts/flash": "^3.2",
        "laravel/socialite": "^5.15",
        "propaganistas/laravel-phone": "^5.3",
        "stevebauman/location": "^7.0",
        "sentry/sentry-laravel": "^4.0",
        "eluceo/ical": "^2.14",
        "sabre/vobject": "^4.5",
        "spatie/laravel-cookie-consent": "^3.3",
        "torann/geoip": "^3.0",
        "geoip2/geoip2": "^3.0",
        "pid/speakingurl": "^0.20",
        "jackiedo/timezonelist": "^5.1",
        "mobiledetect/mobiledetectlib": "^4.8",
        "rinvex/countries": "^9.0"
    },
    "require-dev": {
        "barryvdh/laravel-debugbar": "^3.14",
        "fakerphp/faker": "^1.24",
        "mockery/mockery": "^1.6",
        "phpunit/phpunit": "^11.0",
        "laravel-lang/common": "^6.0"
    }
}
```

---

## Methodology

### Sources

1. **Current state** — parsed from root `composer.json` and `bower.json` in this repository (Laravel 5.3 baseline).
2. **Package metadata** — Packagist version history, GitHub release tags, and README compatibility tables for each package.
3. **Framework requirements** — [Laravel 12 release notes](https://laravel.com/docs/12.x/releases) (PHP ^8.2, Symfony 7, Carbon 3, PHPUnit 11).
4. **EOL / advisory checks** — Packagist abandon flags, `roave/security-advisories` known conflicts, and PHP extension requirements.

### Compatibility Criteria

| Column | Definition |
|--------|------------|
| **PHP 8.4** | Package declares `^8.2` or `^8.4` constraint, or runtime-verified with no deprecated API usage |
| **Laravel 12** | Package README / composer.json requires `illuminate/*` ^12 or is framework-agnostic |
| **Action** | One of: Upgrade, Replace, Remove, Fork, Keep |

### Assessment Process

```
For each package:
  1. Fetch latest stable version on Packagist
  2. Read composer.json require constraints for PHP + illuminate/*
  3. Check abandon flag + last commit date
  4. Search codebase for import count / view usage (grep)
  5. Assign action using decision tree:
       abandoned AND Laravel-coupled → Replace or Remove
       maintained + major gap         → Upgrade
       internal dev-master            → Fork + semver
       asset-only / duplicate         → Remove (move to npm)
  6. Record breaking changes in Notes column
```

### Validation Checklist (post-migration)

- [ ] `composer install` resolves without `--ignore-platform-reqs`
- [ ] `composer audit` reports zero critical/high vulnerabilities
- [ ] `php artisan about` shows Laravel 12.x + PHP 8.4.x
- [ ] No remaining references to removed packages (`grep -r` CI gate)
- [ ] All `dev-master` constraints eliminated from `composer.lock`

---

## Appendix: Package Usage Hints (for migration grep)

Run these from the repo root to size migration effort before starting each phase:

```bash
# Collective HTML / Bootstrapper (expect heavy view usage)
rg "Form::|Html::|Bootstrapper" app/ resources/views --count-matches

# Notifynder
rg "Notifynder|notifiable" app/ --count-matches

# Beautymail
rg "Beautymail|Snowfire" app/ --count-matches

# Intervention v2 API
rg "Intervention\\Image|Image::make|->resize|->crop" app/ --count-matches

# iCal reader
rg "IcalReader|icalreader" app/ --count-matches
```

---

*This document should be updated after each migration phase with actual tested versions and any discovered blockers.*
