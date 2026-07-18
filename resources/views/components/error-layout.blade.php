@props(['code' => '', 'title' => 'Something went wrong'])
{{-- Guest-safe, dependency-free error layout (renders without auth context or
     built assets, so it works for 500/503 too). On-brand CryptoZing styling. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CryptoZing — {{ $title }}</title>
    <x-favicon />
    <style>
        :root {
            --navy: #04254a; --orange: #ff5908;
            --ink: #0f172a; --muted: #475569;
            --bg: #f1f5f9; --card: #ffffff; --border: #e2e8f0;
        }
        @media (prefers-color-scheme: dark) {
            :root {
                --navy: #dbeafe; --ink: #e2e8f0; --muted: #94a3b8;
                --bg: #060d1a; --card: #0c1f38; --border: rgba(255,255,255,.10);
            }
        }
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; padding: 1.5rem;
            display: flex; align-items: center; justify-content: center;
            background: radial-gradient(circle at 18% 12%, rgba(99,102,241,.16) 0, transparent 45%), var(--bg);
            color: var(--ink);
            font-family: ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        .cz-error {
            width: 100%; max-width: 34rem; text-align: center;
            background: var(--card); border: 1px solid var(--border);
            border-radius: 1.25rem; padding: clamp(2rem, 5vw, 3rem);
            box-shadow: 0 20px 45px -25px rgba(4,37,74,.45);
        }
        .cz-error__brand {
            display: inline-flex; align-items: center; gap: .6rem;
            text-decoration: none; color: var(--navy);
            font-weight: 600; font-size: 1.1rem; margin-bottom: 1.75rem;
        }
        .cz-error__brand img { display: block; border-radius: .5rem; }
        .cz-error__code {
            margin: 0; line-height: 1; letter-spacing: -.03em;
            font-size: clamp(3.5rem, 12vw, 5.5rem); font-weight: 800; color: var(--navy);
        }
        .cz-error__title { margin: .5rem 0 0; font-size: 1.5rem; font-weight: 700; color: var(--navy); }
        .cz-error__message { margin: .85rem 0 1.85rem; color: var(--muted); font-size: 1.02rem; line-height: 1.55; }
        .cz-error__detail { display: block; margin-top: .6rem; font-size: .92rem; color: var(--muted); }
        .cz-error__actions { display: flex; flex-wrap: wrap; gap: .75rem; justify-content: center; }
        .cz-btn {
            display: inline-flex; align-items: center; justify-content: center;
            padding: .72rem 1.45rem; border-radius: .7rem; font-weight: 600;
            font-size: .95rem; text-decoration: none; transition: filter .15s ease, background .15s ease;
        }
        .cz-btn--primary { background: var(--orange); color: #fff; }
        .cz-btn--primary:hover { filter: brightness(1.07); }
        .cz-btn--ghost { background: transparent; color: var(--navy); border: 1px solid var(--border); }
        .cz-btn--ghost:hover { background: var(--bg); }
    </style>
</head>
<body>
    <main class="cz-error" role="main">
        <a href="/" class="cz-error__brand">
            <img src="{{ asset('images/CZ.png') }}" alt="CryptoZing" width="48" height="48">
            <span>CryptoZing</span>
        </a>

        <p class="cz-error__code">{{ $code }}</p>
        <h1 class="cz-error__title">{{ $title }}</h1>
        <div class="cz-error__message">{{ $slot }}</div>

        <div class="cz-error__actions">
            @isset($actions)
                {{ $actions }}
            @else
                @auth
                    <a class="cz-btn cz-btn--primary" href="{{ route('dashboard') }}">Back to dashboard</a>
                @else
                    <a class="cz-btn cz-btn--primary" href="/">Home</a>
                    <a class="cz-btn cz-btn--ghost" href="{{ route('login') }}">Sign in</a>
                @endauth
            @endisset
        </div>
    </main>
</body>
</html>
