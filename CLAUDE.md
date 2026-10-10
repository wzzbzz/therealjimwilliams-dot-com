# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a legacy PHP web application — a retro Apple ][+ terminal interface that evolved into a real-time MUD (Multi-User Dungeon). Everything is written from scratch (except jQuery 1.7.1). Development has largely ceased; this repo is preserved for reference. Active development has moved to playdoink.com.

There are no build tools, package managers, or modern tooling. No `composer.json`, no `package.json`, no CI/CD.

## Running Locally

- Requires PHP (the codebase mixes deprecated `mysql_*` and `mysqli_*` calls — PHP 5.x era)
- Requires MySQL/MariaDB with a database named `therealjimwilliams`
- Local hostname must be `local.therealjimwilliams.com` (the DB class switches credentials based on `$_SERVER['HTTP_HOST']`)
- **`bootstrap.php` line 5**: The `BASEPATH` constant is hardcoded to an old path (`/Users/James/Code/blackbart/therealjimwilliams.com/`) — update this for your environment

Database credentials are hardcoded in `_lib/php/database.php`:
- Local (`local.therealjimwilliams.com`): `127.0.0.1`, user `root`, password `vagrant`
- Production: `mysql.therealjimwilliams.com`, user `theduke`, password `atriedes`

### NativeX CLI (Cryptstack.com subapp)

```bash
php nativex/cli.php [key] [text] [action]   # encode/decode with NativeX cipher
php nativex/test.php                         # run NativeX cipher tests
php _lib/php/nativex/test.php               # additional NativeX tests
```

## Architecture

### Request Flow

1. User types into the browser terminal (`index.php` — the Apple ][+ UI)
2. jQuery sends an AJAX POST to `listen.php`
3. `listen.php` bootstraps via `bootstrap.php`, instantiates `Commander`, parses the command
4. Response JSON has shape `{action: "OUTPUT"|"EXECUTE", data: string|javascript}`
   - `OUTPUT`: terminal prints the string
   - `EXECUTE`: terminal `eval()`s JavaScript
5. A parallel poller (`get_events.php`, 1-second interval) fetches live multiplayer events

### Key Directories

- `_classes/commander/` — Command parser and ~40 individual command implementations (one file per command: `go.php`, `look.php`, `eat.php`, etc.)
- `_classes/` — Game logic: `location.php`, `events.php`, `items.php`, `messenger/`, `learner/`
- `_lib/php/` — Shared PHP: `database.php` (DB singleton, mysqli), `functions.php` (encoding utils), `user/`, `nativex/` (cipher library)
- `nativex/` — Cryptstack.com subapp (served as a subdomain); self-contained with its own `index.php`, `cli.php`, `.htaccess` (CORS)

### Database Layer

`DB` class in `_lib/php/database.php` is a mysqli-based singleton. All queries are raw SQL strings — no ORM, no prepared statements. The older `_lib/database.php` uses the removed `mysql_*` API and should not be used.

### Adding a Command

Each terminal command maps to a file in `_classes/commander/commands/`. To add a command, create a new file there following the existing pattern and register it in the commander.
