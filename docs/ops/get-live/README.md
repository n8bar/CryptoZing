# Get Live — Self-Hosting CryptoZing

Run your own CryptoZing on one server with Docker. This is the exact recipe our production deployment runs — same image, same compose file. (For local development with Sail instead, see [`QUICK_START.md`](QUICK_START.md).)

CryptoZing stays **watch-only**: it needs an extended public key (xpub) at most, and nothing here ever asks for a private key or seed phrase.

## What you need

- A Linux server with Docker Engine and the compose plugin (2 GB RAM minimum, 4 GB comfortable).
- A domain name pointed at the server.
- Ports 80 and 443 open.
- A Mailgun account if you want outbound mail (invoices by email); the app runs without it.

## 1. Get the files

```bash
mkdir -p /opt/cryptozing && cd /opt/cryptozing
curl -fsSLO https://raw.githubusercontent.com/n8bar/CryptoZing/main/compose.production.yaml
mkdir -p docker/production/nginx docker/production/mysql storage certbot-webroot
curl -fsSL -o docker/production/mysql/prod.cnf https://raw.githubusercontent.com/n8bar/CryptoZing/main/docker/production/mysql/prod.cnf
curl -fsSL --create-dirs -o docker/production/nginx/templates/default.conf.template \
  https://raw.githubusercontent.com/n8bar/CryptoZing/main/docker/production/nginx/templates/default.conf.template
```

(Or clone the repo and copy the same paths.)

## 2. TLS certificate

Issue a certificate on the host before first start (nginx expects it):

```bash
dnf install -y certbot   # or apt install certbot
certbot certonly --standalone -d your.domain --agree-tos -m you@example.com -n
```

Renewals after the stack is up use the webroot the compose file mounts:

```bash
# in /etc/letsencrypt/renewal/your.domain.conf, switch to:
#   authenticator = webroot
#   webroot_path = /opt/cryptozing/certbot-webroot
```

## 3. Configure

Create `/opt/cryptozing/.env`, starting from the repo's `.env.example`. The values that matter for production:

```dotenv
APP_NAME=CryptoZing
APP_ENV=production
APP_KEY=            # generated in step 4 — never reuse someone else's
APP_DEBUG=false
APP_URL=https://your.domain
APP_PUBLIC_URL=${APP_URL}
TRUSTED_PROXIES=172.16.0.0/12   # the compose network's proxy (front nginx)

ALPHA_GATE_ENABLED=false

LOG_CHANNEL=stack
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=cryptozing
DB_USERNAME=cryptozing
DB_PASSWORD=<strong password>
DB_ROOT_PASSWORD=<different strong password>

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=mailgun               # or log, to run without mail
MAILGUN_DOMAIN=
MAILGUN_SECRET=
MAIL_FROM_ADDRESS=no-reply@your.domain
MAIL_OUTBOUND_ENABLED=false       # flip on once mail is configured
MAIL_ALIAS_ENABLED=false

SUPPORT_AGENT_EMAILS=you@example.com
DONATION_WALLET_XPUB=             # blank = no /donate page; Taproot needs tr(xpub.../0/*)

# compose-level settings
CZ_SERVER_NAME=your.domain
CZ_TAG=latest
```

## 4. First start

```bash
cd /opt/cryptozing
docker compose -f compose.production.yaml pull

# generate the app key into .env
docker compose -f compose.production.yaml run --rm --no-deps app php artisan key:generate --show
# paste the output into .env as APP_KEY=...

# run migrations once, before services start
docker compose -f compose.production.yaml run --rm app php artisan migrate --force

docker compose -f compose.production.yaml up -d
```

Visit `https://your.domain`, register, then connect a wallet xpub at `/wallet/settings`.

## Updating

```bash
cd /opt/cryptozing
docker compose -f compose.production.yaml pull
docker compose -f compose.production.yaml run --rm app php artisan migrate --force
docker compose -f compose.production.yaml up -d
```

Pin `CZ_TAG` to a commit-sha tag instead of `latest` if you want explicit, rollback-able releases.

## Backups

The database lives in the `dbdata` volume. A nightly dump kept off the server is the minimum:

```bash
docker compose -f compose.production.yaml exec db \
  sh -c 'mysqldump -ucryptozing -p"$MYSQL_PASSWORD" cryptozing' | gzip > backup-$(date +%F).sql.gz
```

Uploads and logs live in `./storage` — include it in file backups.

## Notes

- Every service restarts automatically (`restart: unless-stopped`) — a reboot brings the whole stack back.
- The queue worker and scheduler run as their own containers off the same image; the payment watcher runs from the scheduler every minute.
- Container logs are capped (10 MB × 3 files per service).
- The image runs as a non-root user (uid 1000) and ships no `.env`, no dev dependencies, and no CryptoZing site content.
