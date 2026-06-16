<?php
/**
 * Separate the real CZ.png into two bilevel masks (navy, orange) so potrace can
 * trace each color faithfully. No redraw — every pixel comes from the artwork.
 */
$src = '/var/www/html/public/images/CZ.png';
$dir = '/var/www/html/storage/app/branding';

$im = new Imagick($src);
$im->setImageFormat('png32');
$w = $im->getImageWidth();
$h = $im->getImageHeight();

$px = $im->exportImagePixels(0, 0, $w, $h, 'RGBA', Imagick::PIXEL_CHAR);

$navyRef   = [0x04, 0x25, 0x4a];
$orangeRef = [0xff, 0x59, 0x08];
$whiteRef  = [0xff, 0xff, 0xff]; // anti-alias halo / background -> not traced

$navy = array_fill(0, $w * $h, 255);   // 0 = foreground (black) for potrace, 255 = bg
$orange = array_fill(0, $w * $h, 255);

for ($i = 0, $p = 0; $i < $w * $h; $i++, $p += 4) {
    $a = $px[$p + 3];
    if ($a < 100) {
        continue; // transparent -> background in both
    }
    $r = $px[$p];
    $g = $px[$p + 1];
    $b = $px[$p + 2];
    $dn = ($r - $navyRef[0]) ** 2 + ($g - $navyRef[1]) ** 2 + ($b - $navyRef[2]) ** 2;
    $do = ($r - $orangeRef[0]) ** 2 + ($g - $orangeRef[1]) ** 2 + ($b - $orangeRef[2]) ** 2;
    $dw = ($r - $whiteRef[0]) ** 2 + ($g - $whiteRef[1]) ** 2 + ($b - $whiteRef[2]) ** 2;
    if ($dw <= $dn && $dw <= $do) {
        continue; // closest to white -> background, trace nothing
    }
    if ($dn <= $do) {
        $navy[$i] = 0;
    } else {
        $orange[$i] = 0;
    }
}

function writeMask(array $data, int $w, int $h, string $path): void
{
    $m = new Imagick();
    $m->newImage($w, $h, new ImagickPixel('white'));
    $m->setImageDepth(8);
    $m->importImagePixels(0, 0, $w, $h, 'I', Imagick::PIXEL_CHAR, $data);
    $m->setImageFormat('bmp');
    $m->writeImage($path);
}

// Silhouette = anywhere the mark is painted (navy OR orange foreground).
$sil = array_fill(0, $w * $h, 255);
for ($i = 0; $i < $w * $h; $i++) {
    if ($navy[$i] === 0 || $orange[$i] === 0) {
        $sil[$i] = 0;
    }
}

writeMask($navy, $w, $h, "$dir/mask-navy.bmp");
writeMask($orange, $w, $h, "$dir/mask-orange.bmp");
writeMask($sil, $w, $h, "$dir/mask-silhouette.bmp");
echo "masks written ({$w}x{$h})\n";
