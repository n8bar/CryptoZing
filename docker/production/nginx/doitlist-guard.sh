#!/bin/sh
# Do It List shares this proxy. Its server block may only join the config
# when nginx still accepts it, so a broken or missing Do It List file can
# never keep CryptoZing from starting or reloading.
#
# Runs at container start (from /docker-entrypoint.d) and on every Do It List
# routing change: docker exec cz-nginx /docker-entrypoint.d/90-doitlist-guard.sh --strict
# At start it always exits 0, since a failing entrypoint script stops nginx;
# --strict exits 1 when it turned the new block away.
set -u
status=0
[ "${1:-}" = "--strict" ] && status=1

src=/etc/nginx/doitlist/doitlist.conf
live=/etc/nginx/conf.d/doitlist.conf
prev=/tmp/doitlist.conf.prev

# At start, stderr is already the container log. Under docker exec it is only
# the caller's console, so --strict also writes to the container log.
log() {
    echo "doitlist-guard: $*" >&2
    [ "$status" = 1 ] && echo "doitlist-guard: $*" > /proc/1/fd/2 2>/dev/null
    return 0
}

rm -f "$prev"
[ -f "$live" ] && cp "$live" "$prev"

if [ -f "$src" ]; then
    cp "$src" "$live"
else
    rm -f "$live"
fi

if err=$(nginx -t -q 2>&1); then
    exit 0
fi

log "Do It List's server block failed nginx -t; keeping the last good one"
log "$err"
if [ -f "$prev" ]; then
    cp "$prev" "$live"
    nginx -t -q 2>/dev/null && exit "$status"
fi
rm -f "$live"
log "serving without Do It List"
exit "$status"
