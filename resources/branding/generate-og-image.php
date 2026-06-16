<?php
/**
 * Build the 1200×630 social card (og:image), matching the favicon mark.
 * Navy brand background + the keyline CZ mark + wordmark + tagline.
 * Source SVG -> rendered PNG via rsvg-convert.
 */
$pub = '/var/www/html/public';
$out = '/var/www/html/storage/app/branding';

// Embed the shipped keyline mark (favicon.svg) as a data URI so it stays vector.
// Thin the keyline for the larger card — the favicon's weight is tuned for tiny
// sizes and reads heavy at 340px. (Edits this card only, not the favicon.)
$markSvg = str_replace('stroke-width="600"', 'stroke-width="430"', file_get_contents("$pub/favicon.svg"));
$mark = 'data:image/svg+xml;base64,' . base64_encode($markSvg);

$wordmark = 'CryptoZing';
$tagline1 = 'Bitcoin invoicing with';
$tagline2 = 'on-chain payment tracking';
$url = 'cryptozing.app';

$svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="1200" height="630" viewBox="0 0 1200 630">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="#0a3a66"/>
      <stop offset="55%" stop-color="#04254a"/>
      <stop offset="100%" stop-color="#02152b"/>
    </linearGradient>
    <radialGradient id="glow" cx="26%" cy="50%" r="42%">
      <stop offset="0%" stop-color="#ff5908" stop-opacity="0.20"/>
      <stop offset="100%" stop-color="#ff5908" stop-opacity="0"/>
    </radialGradient>
  </defs>
  <rect width="1200" height="630" fill="url(#bg)"/>
  <rect width="1200" height="630" fill="url(#glow)"/>

  <image x="92" y="145" width="340" height="340" xlink:href="$mark"/>

  <g font-family="DejaVu Sans" fill="#ffffff">
    <text x="470" y="262" font-size="96" font-weight="bold" letter-spacing="-1">$wordmark</text>
    <rect x="474" y="291" width="124" height="9" rx="4.5" fill="#ff5908"/>
    <text x="472" y="356" font-size="34" fill="#cbd5e1">$tagline1</text>
    <text x="472" y="402" font-size="34" fill="#cbd5e1">$tagline2</text>
    <text x="472" y="470" font-size="28" font-weight="bold" fill="#ff8a4d">$url</text>
  </g>
</svg>
SVG;

file_put_contents("$out/og-preview.svg", $svg);
exec('rsvg-convert -w 1200 -h 630 ' . escapeshellarg("$out/og-preview.svg") . ' -o ' . escapeshellarg("$out/og-preview.png"), $o, $rc);
echo $rc === 0 ? "built og-preview.png\n" : "rsvg failed\n";
