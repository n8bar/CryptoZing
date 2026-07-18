# Branding — CryptoZing favicon pipeline

The favicons in `public/` are generated from the **CryptoZing CZ mark**, not
hand-drawn. `cz-trace.svg` is the faithful vector master: a `potrace`
vectorization of the source artwork `public/images/CZ.png`, color-separated into
navy (`#04254a`) and orange (`#ff5908`) plus a white-keyline silhouette.

## Deliverables (in `public/`)

- `favicon.ico` — multi-resolution 16/32/48
- `favicon.svg` — scalable, white keyline, viewBox tightened to the mark
- `favicon-16x16.png`, `favicon-32x32.png`
- `apple-touch-icon.png` — 180, opaque white background (iOS renders transparency as black)
- `android-chrome-192x192.png`, `android-chrome-512x512.png`
- `site.webmanifest`

A per-size **white keyline** is applied so the navy C stays legible on dark
browser chrome; its width is chosen per size (heavier at small sizes) rather
than scaled linearly, so it doesn't vanish at 16px.

The set is referenced site-wide via `resources/views/components/favicon.blade.php`
(`<x-favicon />`), included in all three layouts. (The previous per-page
`x-emoji-favicon` component was retired — one brand mark everywhere.)

## Regenerating

Run inside the Sail container (needs `potrace` + `rsvg-convert`; the PHP scripts
use `Imagick`). Paths in the scripts are container-absolute (`/var/www/html/...`).

```bash
# one-time: install potrace in the container
docker compose exec -u root laravel.test apt-get install -y potrace

# 1. separate the artwork into navy / orange / silhouette masks
./vendor/bin/sail php resources/branding/make-masks.php

# 2. trace each mask (run from the masks' directory)
potrace mask-navy.bmp       --svg --color "#04254a" -t 30 -O 0.4 -a 1.2 -o trace-navy.svg
potrace mask-orange.bmp     --svg --color "#ff5908" -t 30 -O 0.4 -a 1.2 -o trace-orange.svg
potrace mask-silhouette.bmp --svg --color "#ffffff" -t 30 -O 0.4 -a 1.2 -o trace-silhouette.svg

# 3. combine navy + orange into the master
./vendor/bin/sail php resources/branding/combine-trace.php   # -> cz-trace.svg

# 4. package all deliverables into public/
./vendor/bin/sail php resources/branding/generate-favicons.php
```

To change the mark, replace `public/images/CZ.png` and re-run the pipeline.

## Social card (og:image)

`generate-og-image.php` builds the 1200×630 social-preview card that matches the
favicon mark: navy brand background, the keyline CZ glyph (embedded from
`favicon.svg`, keyline thinned for the larger scale), wordmark, and tagline.

```bash
./vendor/bin/sail php resources/branding/generate-og-image.php
# then copy the result to the Pages surface:
cp storage/app/branding/og-preview.png storage/app/branding/og-preview.svg site/
```

Output: `site/og-preview.png` (+ `og-preview.svg` source). The marketing site
references it via `og:image` / `twitter:image` in `site/_layouts/default.njk`.
The Laravel app has no social-card surface, so nothing in `public/` is needed.
