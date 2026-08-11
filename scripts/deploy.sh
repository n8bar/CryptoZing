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
# Our alpha deployment layers the site container in; self-hosters won't have it.
[ -f compose.alpha.yaml ] && FILES+=(-f compose.alpha.yaml)

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
