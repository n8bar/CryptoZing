{{-- A $0-or-less invoice's QR code, crossed out in red (LINE_ITEMS.md §4). Expects $size in px. --}}
<div role="img" aria-label="No payment QR code: nothing to pay"
     style="position:relative; display:inline-block; width:{{ $size }}px; height:{{ $size }}px; overflow:hidden; -webkit-print-color-adjust:exact; print-color-adjust:exact;">
    <div style="opacity:0.3;">{!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size($size)->margin(0)->generate('Nothing to pay') !!}</div>
    <div style="position:absolute; left:-25%; right:-25%; top:50%; transform:translateY(-50%) rotate(-30deg); background:#dc2626; color:#fff; font-weight:800; text-align:center; padding:{{ round($size / 30) }}px 0; font-size:{{ round($size / 10) }}px; line-height:1.2; letter-spacing:0.02em;">
        Nothing to pay
    </div>
</div>
