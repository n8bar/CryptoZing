#!/usr/bin/env bash
# Production deploy (M20.2 §4.2): pull a published tag, migrate once in a
# one-shot container, roll services onto the tag, and prove nothing was left
# behind on the old one. Non-interactive by design — run as the deploy user
# from the compose directory:
#
#   ./deploy.sh <commit-sha-tag|latest>
#
# Rollback is this same script with the previous tag (§4.4).
set -euo pipefail

TAG="${1:?usage: deploy.sh <image-tag>}"
IMAGE="ghcr.io/n8bar/cryptozing-app"

FILES=(-f compose.production.yaml)
# Our deployment layers the site container in; self-hosters won't have it.
[ -f compose.alpha.yaml ] && FILES+=(-f compose.alpha.yaml)

# Our deployment sits behind the shared front door (its own stack). Neither
# stack owns the frontdoor network; make sure it exists so a missing one can't
# stop cz-nginx starting. The subnet is fixed: cz-nginx trusts forwarded
# client addresses only from it.
if [ -f compose.alpha.yaml ] && ! docker network inspect frontdoor > /dev/null 2>&1; then
    docker network create --subnet 172.30.0.0/24 frontdoor > /dev/null
fi

# Persist the tag so later compose invocations keep serving it.
if grep -q '^CZ_TAG=' .env; then
    sed -i "s|^CZ_TAG=.*|CZ_TAG=${TAG}|" .env
else
    printf 'CZ_TAG=%s\n' "$TAG" >> .env
fi

docker compose "${FILES[@]}" pull

# Migrations run against the new code before any service switches to it.
docker compose "${FILES[@]}" run --rm app php artisan migrate --force

docker compose "${FILES[@]}" up -d --remove-orphans

# Hand the front door our blocks for the public names. Its guard loads them
# only if nginx accepts them, and keeps the last good ones otherwise.
if [ -f compose.alpha.yaml ]; then
    public=$(grep '^CZ_PUBLIC_SERVER_NAME=' .env | cut -d= -f2-)
    mkdir -p frontdoor
    sed "s|\${CZ_PUBLIC_SERVER_NAME}|${public:?set CZ_PUBLIC_SERVER_NAME in .env}|g" \
        docker/production/frontdoor/cryptozing.conf.template > frontdoor/cryptozing.conf.new
    mv frontdoor/cryptozing.conf.new frontdoor/cryptozing.conf
    if docker ps --format '{{.Names}}' | grep -qx frontdoor; then
        docker exec frontdoor /docker-entrypoint.d/90-sites-guard.sh --strict cryptozing \
            || echo "WARN: the front door turned our new blocks away; see docker logs frontdoor" >&2
    fi
fi

# Recreating the scheduler mid-run strands its withoutOverlapping mutex (#188).
docker compose "${FILES[@]}" exec -T app php artisan schedule:clear-cache \
    || echo "WARN: schedule:clear-cache failed; clear framework/schedule-* rows from cache_locks by hand (#188)" >&2

# §4.2.4: nothing may still be running another tag of the app image.
stale=$(docker ps --format '{{.Names}} {{.Image}}' \
    | awk -v img="$IMAGE" -v want="$IMAGE:$TAG" '$2 ~ "^"img && $2 != want')
if [ -n "$stale" ]; then
    echo "ERROR: containers left on another tag:" >&2
    echo "$stale" >&2
    exit 1
fi

# Drop dangling layers only — previous tagged images stay pullable for rollback.
docker image prune -f > /dev/null

echo "Deployed ${IMAGE}:${TAG}"
