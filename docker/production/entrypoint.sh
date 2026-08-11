#!/bin/sh
# Shared entrypoint for every service that runs the app image.
# Migrations are deliberately NOT run here — the deploy runs them once,
# in a one-shot container, before services roll (M20.2 §4.2.2).
set -e

cd /var/www/html

# A fresh storage bind mount is empty — lay down the skeleton Laravel expects.
for d in app/public framework/cache/data framework/sessions framework/views logs; do
    mkdir -p "storage/$d"
done

# Refresh the shared volume nginx serves static assets from, when mounted.
if [ -d /shared/public ]; then
    rm -rf /shared/public/..?* /shared/public/.[!.]* /shared/public/* 2>/dev/null || true
    # Plain -R: preserving times/ownership on a volume root we may not own
    # turns into a fatal error under set -e.
    cp -R public/. /shared/public/
fi

# Cache config/routes/views against the runtime environment.
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
