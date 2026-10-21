<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SubSync — Design System</title>
<script>window.APP_BASE = '{{ request()->getBaseUrl() }}';</script>
<script src="{{ asset('js/portal-context.js') . '?v=' . filemtime(public_path('js/portal-context.js')) }}"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/ui-polish.css') . '?v=' . filemtime(public_path('css/ui-polish.css')) }}">
@vite('resources/css/app.css')
<style>
    body { background: var(--pro-canvas, #FAF6F0); color: var(--text, #26231F); font-family: var(--font-sans); margin: 0; }
    .ds-wrap { max-width: 1080px; margin: 0 auto; padding: var(--space-7) var(--space-5) var(--space-8); }
    .ds-head h1 { font-family: var(--font-serif); font-size: 32px; font-weight: 400; margin: 0 0 4px; }
    .ds-head p { color: var(--text-dim); font-size: 14px; margin: 0 0 var(--space-6); }
    .ds-section { margin-bottom: var(--space-7); }
    .ds-section h2 { font-size: 13px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--text-dim); margin: 0 0 var(--space-3); }
    .ds-row { display: flex; flex-wrap: wrap; gap: var(--space-3); align-items: center; }
    .ds-swatch { width: 140px; border-radius: var(--radius-sm); overflow: hidden; border: 1px solid var(--pro-border, #E7E1D8); background: #fff; font-size: 11px; }
    .ds-swatch i { display: block; height: 44px; }
    .ds-swatch span { display: block; padding: 6px 8px; color: var(--text-dim); }
    .ds-demo { background: #fff; border: 1px solid var(--pro-border, #E7E1D8); border-radius: var(--radius-md); padding: var(--space-5); box-shadow: var(--shadow-1); }
    .ds-icon-cell { width: 64px; text-align: center; font-size: 10px; color: var(--text-dim); padding: var(--space-2) 0; }
    .ds-icon-cell .ui-icon { color: var(--accent); }
</style>
</head>
<body>
<div class="ds-wrap">

    <div class="ds-head">
        <h1>SubSync Design System</h1>
        <p>Canonical tokens from <code>public/css/ui-polish.css</code>, mirrored by the Tailwind&nbsp;v4 + daisyUI kit
           (<code>resources/css/app.css</code>). Kit classes are namespaced <code>d-</code>; Tailwind utilities use <code>tw-</code>.</p>
    </div>

    <section class="ds-section">
        <h2>Color tokens</h2>
        <div class="ds-row">
            @foreach ([
                ['--accent', '#86653D'], ['--text', '#26231F'], ['--text-mid', '#45413C'], ['--text-dim', '#5F5A53'],
                ['--blue', '#3E6A8F'], ['--green', '#4F7A57'], ['--yellow', '#946E1E'], ['--purple', '#6E6486'], ['--danger', '#A84545'],
            ] as [$token, $hex])
            <div class="ds-swatch"><i style="background: {{ $hex }}"></i><span>{{ $token }}<br>{{ $hex }}</span></div>
            @endforeach
        </div>
    </section>

    <section class="ds-section">
        <h2>Radii &amp; elevation</h2>
        <div class="ds-row">
            <div class="ds-demo" style="border-radius: var(--radius-sm);">--radius-sm · buttons, inputs</div>
            <div class="ds-demo" style="border-radius: var(--radius-md);">--radius-md · cards, popovers</div>
            <div class="ds-demo" style="border-radius: var(--radius-lg);">--radius-lg · modals</div>
            <div class="ds-demo" style="border-radius: var(--radius-full);">--radius-full · pills</div>
        </div>
        <div class="ds-row" style="margin-top: var(--space-3);">
            <div class="ds-demo" style="box-shadow: var(--shadow-1);">--shadow-1</div>
            <div class="ds-demo" style="box-shadow: var(--shadow-2);">--shadow-2</div>
            <div class="ds-demo" style="box-shadow: var(--shadow-3);">--shadow-3</div>
        </div>
    </section>

    <section class="ds-section">
        <h2>Buttons — app (.btn)</h2>
        <div class="ds-demo ds-row">
            <button class="btn">Default</button>
            <button class="btn" style="background:var(--accent);border-color:var(--accent);color:#fff;">Accent</button>
            <button class="btn" style="background:#4F7A57;border-color:#4F7A57;color:#fff;">Success</button>
            <button class="btn" style="background:#A84545;border-color:#A84545;color:#fff;">Danger</button>
            <button class="btn" disabled>Disabled</button>
        </div>
    </section>

    <section class="ds-section">
        <h2>Buttons — kit (.d-btn)</h2>
        <div class="ds-demo ds-row">
            <button class="d-btn d-btn-primary">Primary</button>
            <button class="d-btn d-btn-secondary">Secondary</button>
            <button class="d-btn d-btn-accent">Accent</button>
            <button class="d-btn d-btn-outline">Outline</button>
            <button class="d-btn d-btn-ghost">Ghost</button>
            <button class="d-btn d-btn-sm d-btn-primary">Small</button>
        </div>
    </section>

    <section class="ds-section">
        <h2>Cards</h2>
        <div class="ds-row" style="align-items: stretch;">
            <div class="card" style="flex:1 1 280px;"><h3 style="margin:0 0 6px;">App card</h3><div class="card-subtitle">.card — the shared portal container.</div></div>
            <div class="d-card d-card-border" style="flex:1 1 280px;background:#fff;"><div class="d-card-body"><h3 class="d-card-title">Kit card</h3><p style="margin:0;">.d-card — daisyUI, themed to the same palette.</p></div></div>
        </div>
    </section>

    <section class="ds-section">
        <h2>Badges &amp; pills</h2>
        <div class="ds-demo ds-row">
            <span class="pill pill-active">Active</span>
            <span class="pill pill-pending">Pending</span>
            <span class="pill pill-urgent">Urgent</span>
            <span class="pill pill-resolved">Resolved</span>
            <span class="d-badge d-badge-primary">Kit primary</span>
            <span class="d-badge d-badge-soft d-badge-success">Kit success</span>
            <span class="d-badge d-badge-outline">Kit outline</span>
        </div>
    </section>

    <section class="ds-section">
        <h2>Forms</h2>
        <div class="ds-demo" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:var(--space-4);">
            <label style="font-size:12px;color:var(--text-dim);">App input
                <input type="text" placeholder="Full name" style="width:100%;margin-top:4px;">
            </label>
            <label style="font-size:12px;color:var(--text-dim);">Kit input
                <input class="d-input d-input-bordered" type="text" placeholder="Full name" style="width:100%;margin-top:4px;">
            </label>
            <label style="font-size:12px;color:var(--text-dim);">Kit select
                <select class="d-select d-select-bordered" style="width:100%;margin-top:4px;">
                    <option>Option A</option><option>Option B</option>
                </select>
            </label>
        </div>
    </section>

    <section class="ds-section">
        <h2>Alerts</h2>
        <div style="display:grid;gap:var(--space-3);">
            <div class="d-alert d-alert-info">Info — the kit mirrors the app palette.</div>
            <div class="d-alert d-alert-success">Success — documents issued.</div>
            <div class="d-alert d-alert-warning">Warning — dues due soon.</div>
            <div class="d-alert d-alert-error">Error — payment validation failed.</div>
        </div>
    </section>

    <section class="ds-section">
        <h2>Icons — &lt;x-icon&gt; / SubUI.icon()</h2>
        <div class="ds-demo ds-row" style="gap:var(--space-2);">
            @foreach (['home','chart-bar','chart-line','users','user','shield','wrench','map','megaphone','scroll','folder','archive','credit-card','message','lightbulb','gear','sliders','search','bell','coins','alert','mail','clipboard','map-pin','phone','lock','key','camera','paperclip','send','upload','calendar','building','plus','check','x','pencil','save','trash','info'] as $name)
            <div class="ds-icon-cell"><x-icon name="{{ $name }}" size="20"/><div>{{ $name }}</div></div>
            @endforeach
        </div>
    </section>

    <section class="ds-section">
        <h2>Empty state</h2>
        <div class="ds-demo">
            <div class="empty-state">
                <div class="empty-icon"><x-icon name="message" size="28"/></div>
                <div class="empty-title">No messages yet</div>
                <div class="empty-hint">Start a conversation and it will appear here instantly over the realtime connection.</div>
            </div>
        </div>
    </section>

</div>
</body>
</html>
