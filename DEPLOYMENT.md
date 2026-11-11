# SubSync — Deployment Guide

Prepared 2026-10-04. Two supported paths: **A) VPS + Supervisor (no Docker)** and
**B) Docker Compose**. Both run the same four processes: **web**, **queue worker**,
**scheduler**, and **Reverb** (WebSocket server). SSE + polling remain as fallbacks.

> Reminder (dev machines only): never run `php artisan config:cache` on a machine where you also
> run `php artisan test` — a cached config overrides `phpunit.xml` env and `RefreshDatabase`
> will wipe the **real** database.

---

## 0. What must change for production

| Concern | Dev (now) | Production |
|---|---|---|
| Env | `APP_ENV=local`, `APP_DEBUG=true` | `APP_ENV=production`, `APP_DEBUG=false` |
| URL | ngrok / `subsync.test` | `APP_URL=https://your-domain` |
| Database | SQLite | **MySQL/MariaDB or Postgres** |
| Session/Cache/Queue | `file` / `file` / `database` | **Redis** (or `database`) |
| Files | local `public` disk | local persistent disk, or **S3** |
| Assets | `npm run dev` | `npm run build` |

**Priority:** moving off SQLite is the single most important change (single-writer + DB session/cache
was the top bottleneck — see `docs/SCALABILITY.md`).

---

## 1. Production `.env` (key values)

```dotenv
APP_NAME=SubSync
APP_ENV=production
APP_DEBUG=false
APP_URL=https://subsync.example.com
APP_KEY=base64:...            # php artisan key:generate

# Database (MySQL example)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=subsync
DB_USERNAME=subsync
DB_PASSWORD=strong-password

# Redis for session/cache/queue (recommended)
SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Broadcasting → Reverb
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=<id>
REVERB_APP_KEY=<key>
REVERB_APP_SECRET=<secret>
REVERB_HOST=subsync.example.com      # public host the browser connects to
REVERB_PORT=443
REVERB_SCHEME=https
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"

# Scaling Reverb across processes/nodes
REVERB_SCALING_ENABLED=true
REVERB_SCALING_CHANNEL=reverb

# Google OAuth (register prod redirect URI in Google Cloud)
GOOGLE_CLIENT_ID=...
GOOGLE_CLIENT_SECRET=...
GOOGLE_REDIRECT_URI=https://subsync.example.com/auth/google/callback
```

> The frontend Echo (`resources/js/echo.js`) already adapts: over HTTPS it connects to
> `wss://<page-host>/app` (proxied), otherwise `ws://localhost:8080`. So no code change per env —
> just make sure the proxy (below) forwards `/app` to Reverb.

---

## 2. Build & migrate (both paths)

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan key:generate        # first deploy only
php artisan migrate --force
php artisan storage:link
php artisan optimize            # config+route+view cache (production only)
```

Health check: `GET /up`.

---

## Path A — VPS + nginx + PHP-FPM + Supervisor (no Docker)

Server: Ubuntu 22.04/24.04. Install `nginx`, `php8.3-fpm` (+ `php8.3-{cli,mysql,redis,mbstring,xml,curl,zip,gd,bcmath}`),
`mysql-server` (or `postgresql`), `redis-server`, `supervisor`, `nodejs`, `npm`, `composer`.

### Files layout
Code at `/var/www/subsync`, owned by `www-data`, and served from `…/public`.

### nginx server block
```nginx
server {
    listen 80;
    server_name subsync.example.com;
    root /var/www/subsync/public;

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Laravel Reverb WebSocket (Pusher protocol at /app)
    location /app {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_read_timeout 60s;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* { deny all; }
}
```
SSL: `sudo certbot --nginx -d subsync.example.com` (then set `APP_URL` to `https://…`).

### Supervisor (`/etc/supervisor/conf.d/subsync.conf`)
```ini
[program:subsync-queue]
command=php /var/www/subsync/artisan queue:work --sleep=3 --tries=3 --timeout=90
directory=/var/www/subsync
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/subsync-queue.log

[program:subsync-schedule]
command=php /var/www/subsync/artisan schedule:work
directory=/var/www/subsync
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/subsync-schedule.log

[program:subsync-reverb]
command=php /var/www/subsync/artisan reverb:start --host=127.0.0.1 --port=8080
directory=/var/www/subsync
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/subsync-reverb.log
```
```bash
sudo supervisorctl reread && sudo supervisorctl update
sudo systemctl restart php8.3-fpm nginx
```

---

## Path B — Docker Compose (optional)

Single image running PHP-FPM + nginx + Supervisor, plus Reverb/queue/scheduler, MySQL, Redis.

### `Dockerfile` (multi-stage: build assets, then run)
```dockerfile
# --- build assets ---
FROM node:20-alpine AS assets
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# --- app ---
FROM php:8.3-fpm-alpine
RUN apk add --no-cache nginx supervisor nodejs npm \
    && docker-php-ext-install pdo_mysql bcmath pcntl gd zip
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY . .
COPY --from=assets /app/public/build ./public/build
RUN composer install --no-dev --optimize-autoloader
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
EXPOSE 80
CMD ["supervisord","-c","/etc/supervisord.conf"]
```

### `docker-compose.yml`
```yaml
services:
  app:
    build: .
    env_file: .env
    ports: ["8080:80"]
    depends_on: [mysql, redis]

  reverb:
    build: .
    command: php artisan reverb:start --host=0.0.0.0 --port=8080
    env_file: .env
    depends_on: [redis]

  queue:
    build: .
    command: php artisan queue:work --sleep=3 --tries=3
    env_file: .env
    depends_on: [mysql, redis]

  scheduler:
    build: .
    command: php artisan schedule:work
    env_file: .env

  mysql:
    image: mysql:8
    environment:
      MYSQL_DATABASE: subsync
      MYSQL_ROOT_PASSWORD: secret
    volumes: ["db:/var/lib/mysql"]

  redis:
    image: redis:7-alpine

volumes: { db: {} }
```
In this setup set `DB_HOST=mysql`, `REDIS_HOST=redis`, and keep `APP_URL`/`REVERB_HOST` at your
public domain, with the reverse proxy (nginx/Caddy/Traefik) forwarding `/app` to the `reverb`
service.

---

## Security hardening (production)
- [ ] **Run `php artisan subsync:check-prod-env`** — it fails while any of the below is unsafe
      (exit code 1; safe to run in CI/pre-deploy).
- [ ] `APP_DEBUG=false`, `APP_ENV=production`, `APP_URL=https://…`.
- [ ] `SESSION_SECURE_COOKIE=true`, `SESSION_ENCRYPT=true` (file sessions in
      `storage/framework/sessions` are readable on disk otherwise).
- [ ] `LOG_LEVEL=warning` (or `error`) — never `debug` in production.
- [ ] Real mail transport (`MAIL_MAILER=smtp`, `mailgun`, …) so receipts/reminders actually send.
- [ ] `APP_TIMEZONE=Asia/Manila` (displayed times + scheduler). Balance reminders are opt-out
      by default; residents can disable email copies in Settings.
- [ ] Serve over HTTPS only; HSTS + CSP are set in `public/.htaccess`.
- [ ] Consider `SESSION_DRIVER=redis` + `CACHE_STORE=redis` (enables cross-session
      invalidation and shared cache across workers).
- [ ] Files uploaded to the private disk (`local`) are streamed by authorized routes; ensure
      `storage/app/private` (and `storage/app/backups`) are not web-served.
- [ ] `subsync:prune-activity-logs` scheduled (RA 10173 retention) and
      `subsync:check-prod-env` in the deploy pipeline.

## Post-deploy checklist
- [ ] `APP_DEBUG=false`, `APP_ENV=production`, correct `APP_URL`.
- [ ] DB migrated (`migrate --force`) and reachable.
- [ ] `npm run build` output present (`public/build/manifest.json`).
- [ ] `storage:link` done; `storage/` + `bootstrap/cache/` writable by the web user.
- [ ] Supervisor (or Compose) running **queue**, **schedule**, **reverb**.
- [ ] `/app` proxied to Reverb; browser connects `wss://<domain>/app`.
- [ ] Google OAuth redirect URI updated to the production callback.
- [ ] SSL enabled; `https` scheme reaches Laravel (trusted proxy / `X-Forwarded-Proto`).
- [ ] `/up` returns 200.

## Notes specific to SubSync
- **`TrustProxies`** is set to `*` (works behind nginx/LB/ngrok). For stricter prod, set it to your
  proxy's IPs in `bootstrap/app.php`.
- **Real-time layers:** Reverb (instant) → SSE (`/api/stream/updates`, ~5s cycle fallback) →
  periodic refresh. If Reverb is down, the app still updates.
- **Never** run `config:cache` on the same instance you run tests on.
