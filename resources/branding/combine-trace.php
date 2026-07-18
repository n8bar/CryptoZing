<?php
/**
 * Merge potrace's per-color SVGs into one faithful two-color master.
 * Both masks share the same canvas, so potrace's coordinate transforms align —
 * we just stack navy (the C, behind) then orange (the Z, in front).
 */
$dir = '/var/www/html/storage/app/branding';

function group(string $svgPath): string
{
    $svg = file_get_contents($svgPath);
    if (preg_match('/<g\b.*?<\/g>/s', $svg, $m)) {
        return $m[0];
    }
    throw new RuntimeException("no <g> in $svgPath");
}

function svgOpenTag(string $svgPath): string
{
    $svg = file_get_contents($svgPath);
    // grab the opening <svg ...> tag verbatim (carries width/height/viewBox)
    if (preg_match('/<svg\b[^>]*>/s', $svg, $m)) {
        return $m[0];
    }
    throw new RuntimeException("no <svg> in $svgPath");
}

$out = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>' . "\n"
    . svgOpenTag("$dir/trace-navy.svg") . "\n"
    . group("$dir/trace-navy.svg") . "\n"
    . group("$dir/trace-orange.svg") . "\n"
    . '</svg>' . "\n";

file_put_contents("$dir/cz-trace.svg", $out);
echo "wrote cz-trace.svg\n";
