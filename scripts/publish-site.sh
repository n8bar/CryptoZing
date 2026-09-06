#!/usr/bin/env bash
# Site-only publish (M21.1 §1.3/§1.6): roll the content container onto a
# published image tag without touching the app services. Run as the deploy
# user from the compose directory:
#
#   ./publish-site.sh <content-repo-commit-sha>
#
# Rollback is this same script with the previous tag, which it prints first.
# App deploys (deploy.sh) leave the site on whatever CZ_SITE_TAG says.
set -euo pipefail

TAG="${1:?usage: publish-site.sh <image-tag>}"
IMAGE="ghcr.io/n8bar/cryptozing-site"
FILES=(-f compose.production.yaml -f compose.alpha.yaml)

previous=$(grep -E '^CZ_SITE_TAG=' .env | cut -d= -f2- || true)
echo "Previous CZ_SITE_TAG: ${previous:-<unset>} (rollback target)"

# Persist the tag so later compose invocations keep serving it.
if grep -q '^CZ_SITE_TAG=' .env; then
    sed -i "s|^CZ_SITE_TAG=.*|CZ_SITE_TAG=${TAG}|" .env
else
    printf 'CZ_SITE_TAG=%s\n' "$TAG" >> .env
fi

docker compose "${FILES[@]}" pull site
docker compose "${FILES[@]}" up -d --no-deps site

# The front nginx resolves `site` per request, so the replacement is live
# as soon as the container is; prove it is the image we asked for.
running=$(docker inspect --format '{{.Config.Image}}' "$(docker compose "${FILES[@]}" ps -q site)")
if [ "$running" != "$IMAGE:$TAG" ]; then
    echo "ERROR: site container is on ${running}, expected ${IMAGE}:${TAG}" >&2
    exit 1
fi

# Drop dangling layers only — previous tagged images stay pullable for rollback.
docker image prune -f > /dev/null

echo "Published ${IMAGE}:${TAG}"
