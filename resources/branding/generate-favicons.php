<?php
/**
 * Promote the faithful trace into shippable favicon deliverables in public/.
 *  - favicon.svg            tightened crop of cz-trace.svg (modern browsers)
 *  - favicon.ico            multi-res 16/32/48
 *  - favicon-16x16.png / -32x32.png
 *  - apple-touch-icon.png   180, opaque white bg + padding (iOS needs opaque)
 *  - android-chrome-192/512 transparent + keyline
 *  - site.webmanifest
 */
$dir = '/var/www/html/storage/app/branding';
$pub = '/var/www/html/public';
$svg = "$dir/cz-trace.svg";

function renderMaster(string $svg, string $png): Imagick
{
    exec('rsvg-convert -w 1024 -h 1024 ' . escapeshellarg($svg) . ' -o ' . escapeshellarg($png), $o, $rc);
    if ($rc !== 0) {
        throw new RuntimeException("rsvg failed: $svg");
    }
    $im = new Imagick($png);
    $im->setImageFormat('png32');
    $im->trimImage(0);
    $im->setImagePage(0, 0, 0, 0);
    return $im;
}

function dilateOver(Imagick $sil, int $r): Imagick
{
    if ($r <= 0) {
        return clone $sil;
    }
    $pass = function (Imagick $img, bool $horiz) use ($r): Imagick {
        $acc = new Imagick();
        $acc->newImage($img->getImageWidth(), $img->getImageHeight(), new ImagickPixel('transparent'));
        $acc->setImageFormat('png32');
        for ($d = -$r; $d <= $r; $d++) {
            $acc->compositeImage($img, Imagick::COMPOSITE_OVER, $horiz ? $d : 0, $horiz ? 0 : $d);
        }
        return $acc;
    };
    return $pass($pass($sil, true), false);
}

function buildIcon(Imagick $src, int $size, int $outline): Imagick
{
    $inner = max(1, $size - 2 * $outline);
    $glyph = clone $src;
    $glyph->resizeImage($inner, $inner, Imagick::FILTER_LANCZOS, 1, true);
    $w = $glyph->getImageWidth();
    $h = $glyph->getImageHeight();

    $pw = $w + 2 * $outline;
    $ph = $h + 2 * $outline;
    $padded = new Imagick();
    $padded->newImage($pw, $ph, new ImagickPixel('transparent'));
    $padded->setImageFormat('png32');
    $padded->compositeImage($glyph, Imagick::COMPOSITE_OVER, $outline, $outline);

    $whiteSil = clone $padded;
    $q = $whiteSil->getQuantumRange()['quantumRangeLong'];
    $whiteSil->evaluateImage(Imagick::EVALUATE_SET, $q, Imagick::CHANNEL_RED | Imagick::CHANNEL_GREEN | Imagick::CHANNEL_BLUE);

    $halo = dilateOver($whiteSil, $outline);
    $halo->compositeImage($padded, Imagick::COMPOSITE_OVER, 0, 0);

    $canvas = new Imagick();
    $canvas->newImage($size, $size, new ImagickPixel('transparent'));
    $canvas->setImageFormat('png32');
    $canvas->compositeImage($halo, Imagick::COMPOSITE_OVER, intdiv($size - $pw, 2), intdiv($size - $ph, 2));
    return $canvas;
}

$master = renderMaster($svg, "$dir/_pkg_master.png");

// outline radius per size (non-linear keyline)
$out = [16 => 1, 32 => 2, 48 => 2, 180 => 6, 192 => 6, 512 => 14];

buildIcon($master, 16, $out[16])->writeImage("$pub/favicon-16x16.png");
buildIcon($master, 32, $out[32])->writeImage("$pub/favicon-32x32.png");
buildIcon($master, 192, $out[192])->writeImage("$pub/android-chrome-192x192.png");
buildIcon($master, 512, $out[512])->writeImage("$pub/android-chrome-512x512.png");

// apple-touch: opaque white bg + ~10% padding (iOS renders transparency as black)
$at = new Imagick();
$at->newImage(180, 180, new ImagickPixel('#ffffff'));
$at->setImageFormat('png32');
$am = clone $master;
$am->resizeImage(150, 150, Imagick::FILTER_LANCZOS, 1, true);
$at->compositeImage($am, Imagick::COMPOSITE_OVER, intdiv(180 - $am->getImageWidth(), 2), intdiv(180 - $am->getImageHeight(), 2));
$at->writeImage("$pub/apple-touch-icon.png");

// favicon.ico multi-res 16/32/48
$ico = new Imagick();
foreach ([16 => 1, 32 => 2, 48 => 2] as $s => $o) {
    $b = buildIcon($master, $s, $o);
    $b->setImageFormat('ico');
    $ico->addImage($b);
}
$ico->setImageFormat('ico');
$ico->writeImages("$pub/favicon.ico", true);

// favicon.svg: layered vector with a white keyline (silhouette stroke) behind
// the navy + orange paths, viewBox tightened to the mark + room for the keyline.
function svgGroup(string $path): string
{
    if (preg_match('/<g\b.*?<\/g>/s', file_get_contents($path), $m)) {
        return $m[0];
    }
    throw new RuntimeException("no <g> in $path");
}

$keyStroke = 600; // path-space units (×0.1 transform); ~30 viewBox units outward
$silG = preg_replace(
    '/stroke="none"/',
    'stroke="#ffffff" stroke-width="' . $keyStroke . '" stroke-linejoin="round" paint-order="stroke"',
    svgGroup("$dir/trace-silhouette.svg"),
    1
);
$navyG = svgGroup("$dir/trace-navy.svg");
$orangeG = svgGroup("$dir/trace-orange.svg");

// bbox of the mark (viewBox units == px), padded enough for the keyline.
exec('rsvg-convert -w 1024 -h 1024 ' . escapeshellarg($svg) . ' -o ' . escapeshellarg("$dir/_pkg_full.png"));
$trim = new Imagick("$dir/_pkg_full.png");
$trim->trimImage(0);
$page = $trim->getImagePage();
$pad = 40;
$vx = $page['x'] - $pad;
$vy = $page['y'] - $pad;
$vw = $trim->getImageWidth() + 2 * $pad;
$vh = $trim->getImageHeight() + 2 * $pad;

$favsvg = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>' . "\n"
    . sprintf('<svg version="1.0" xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="%d %d %d %d" preserveAspectRatio="xMidYMid meet">', $vw, $vh, $vx, $vy, $vw, $vh) . "\n"
    . $silG . "\n" . $navyG . "\n" . $orangeG . "\n"
    . '</svg>' . "\n";
file_put_contents("$pub/favicon.svg", $favsvg);

// site.webmanifest
$manifest = [
    'name' => 'CryptoZing',
    'short_name' => 'CryptoZing',
    'icons' => [
        ['src' => '/android-chrome-192x192.png', 'sizes' => '192x192', 'type' => 'image/png'],
        ['src' => '/android-chrome-512x512.png', 'sizes' => '512x512', 'type' => 'image/png'],
    ],
    'theme_color' => '#04254a',
    'background_color' => '#ffffff',
    'display' => 'standalone',
];
file_put_contents("$pub/site.webmanifest", json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo "packaged into public/\n";
