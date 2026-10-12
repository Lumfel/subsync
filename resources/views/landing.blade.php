<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SubSync is a formal community management portal for Terra Nova Homeowners Association.">
    <title>SubSync | Terra Nova Community Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #282522;
            --muted: #716b64;
            --paper: #fbf8f3;
            --panel: #ffffff;
            --line: #e4ddd3;
            --accent: #9b754b;
            --accent-soft: #f1e8dc;
            --blue: #4f718d;
            --green: #5f8066;
        }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { margin: 0; background: var(--paper); color: var(--ink); font-family: 'DM Sans', sans-serif; }
        a { color: inherit; text-decoration: none; }
        .site-header { max-width: 1180px; margin: 0 auto; padding: 22px 28px; display: flex; align-items: center; justify-content: space-between; gap: 24px; }
        .brand { display: flex; align-items: center; gap: 12px; font-weight: 700; letter-spacing: .04em; }
        .brand img { width: 44px; height: 44px; object-fit: contain; }
        .brand small { display: block; color: var(--muted); font-size: 10px; font-weight: 500; letter-spacing: .12em; text-transform: uppercase; margin-top: 3px; }
        .header-nav { display: flex; align-items: center; gap: 18px; font-size: 13px; color: var(--muted); }
        .header-nav a:hover { color: var(--ink); }
        .header-login { border: 1px solid var(--line); background: var(--panel); border-radius: 7px; padding: 10px 15px; color: var(--ink); font-weight: 600; }
        .hero { max-width: 1180px; margin: 0 auto; padding: 72px 28px 92px; display: grid; grid-template-columns: minmax(0, 1.1fr) minmax(300px, .9fr); gap: 72px; align-items: center; }
        .eyebrow { color: var(--accent); font-size: 11px; font-weight: 700; letter-spacing: .15em; text-transform: uppercase; margin-bottom: 18px; }
        h1, h2 { font-family: 'DM Serif Display', Georgia, serif; font-weight: 400; }
        h1 { font-size: clamp(42px, 6vw, 76px); line-height: 1.02; letter-spacing: .01em; margin: 0 0 22px; max-width: 720px; }
        .hero-copy { max-width: 620px; color: var(--muted); font-size: 17px; line-height: 1.75; margin: 0 0 30px; }
        .actions { display: flex; flex-wrap: wrap; gap: 10px; }
        .button { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 0 18px; border: 1px solid var(--ink); border-radius: 7px; background: var(--ink); color: #fff; font-size: 13px; font-weight: 700; }
        .button.secondary { background: var(--panel); border-color: var(--line); color: var(--ink); }
        .button:hover { transform: translateY(-1px); }
        .hero-panel { background: var(--panel); border: 1px solid var(--line); border-radius: 12px; padding: 28px; box-shadow: 0 18px 42px rgba(54, 45, 35, .07); }
        .panel-label { color: var(--muted); font-size: 10px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; margin-bottom: 20px; }
        .portal-row { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 15px 0; border-top: 1px solid var(--line); }
        .portal-row strong { display: block; font-size: 14px; }
        .portal-row span { display: block; color: var(--muted); font-size: 12px; margin-top: 3px; }
        .portal-arrow { color: var(--accent); font-size: 20px; }
        .section { border-top: 1px solid var(--line); }
        .section-inner { max-width: 1180px; margin: 0 auto; padding: 74px 28px; }
        .section-heading { max-width: 620px; margin-bottom: 32px; }
        h2 { font-size: 38px; line-height: 1.15; margin: 0 0 12px; }
        .section-heading p { color: var(--muted); line-height: 1.7; margin: 0; }
        .feature-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
        .feature { background: var(--panel); border: 1px solid var(--line); border-radius: 9px; padding: 22px; min-height: 180px; }
        .feature-number { color: var(--accent); font-family: 'DM Serif Display', Georgia, serif; font-size: 28px; }
        .feature h3 { font-size: 15px; margin: 16px 0 8px; }
        .feature p { color: var(--muted); font-size: 13px; line-height: 1.65; margin: 0; }
        .trust-band { background: var(--ink); color: #fff; }
        .trust-band .section-inner { display: flex; align-items: center; justify-content: space-between; gap: 28px; }
        .trust-band h2 { margin-bottom: 0; }
        .trust-band p { color: #d2cbc1; font-size: 13px; line-height: 1.6; max-width: 480px; margin: 0; }
        footer { max-width: 1180px; margin: 0 auto; padding: 24px 28px 38px; color: var(--muted); font-size: 11px; display: flex; justify-content: space-between; gap: 16px; }
        @media (max-width: 800px) {
            .header-nav a:not(.header-login) { display: none; }
            .hero { grid-template-columns: 1fr; gap: 36px; padding-top: 48px; }
            .feature-grid { grid-template-columns: 1fr 1fr; }
            .trust-band .section-inner { display: block; }
            .trust-band p { margin-top: 16px; }
        }
        @media (max-width: 500px) {
            .site-header, .hero, .section-inner, footer { padding-left: 18px; padding-right: 18px; }
            h1 { font-size: 46px; }
            .feature-grid { grid-template-columns: 1fr; }
            footer { display: block; }
            footer span { display: block; margin-top: 6px; }
        }
    </style>
</head>
<body>
    <header class="site-header">
        <a class="brand" href="{{ url('/') }}" aria-label="SubSync home">
            <img src="{{ asset('emblem_1.svg') }}" alt="SubSync emblem">
            <span>SubSync<small>Terra Nova Homeowners Association</small></span>
        </a>
        <nav class="header-nav" aria-label="Primary navigation">
            <a href="#capabilities">Capabilities</a>
            <a href="#access">Portal access</a>
            <a class="header-login" href="{{ route('login') }}">Resident / Officer login</a>
        </nav>
    </header>

    <main>
        <section class="hero">
            <div>
                <div class="eyebrow">A formal community management portal</div>
                <h1>One organized platform for the Terra Nova community.</h1>
                <p class="hero-copy">SubSync is a web-based community management platform for residential subdivisions. It strengthens resident engagement through organized communication, issue reporting, recommendations, and interactive subdivision mapping &mdash; giving residents, officers, and administrators a single, reliable community record.</p>
                <div class="actions">
                    <a class="button" href="{{ route('login') }}">Enter resident portal</a>
                </div>
            </div>
            <aside class="hero-panel" id="access" aria-label="Portal access options">
                <div class="panel-label">Portal access</div>
                <a class="portal-row" href="{{ route('login') }}"><span><strong>Residents and officers</strong><span>Submit concerns, view notices, and communicate with the HOA.</span></span><b class="portal-arrow">&rarr;</b></a>
            </aside>
        </section>

        <section class="section" id="capabilities">
            <div class="section-inner">
                <div class="section-heading"><div class="eyebrow">One shared community record</div><h2>Designed around the work Terra Nova needs to do.</h2><p>SubSync replaces scattered notices and informal follow-up with structured, accessible workflows.</p></div>
                <div class="feature-grid">
                    <article class="feature"><div class="feature-number">01</div><h3>Communication</h3><p>Direct correspondence and official announcements keep residents and administrators aligned.</p></article>
                    <article class="feature"><div class="feature-number">02</div><h3>Issue reporting</h3><p>Residents can document concerns while administrators track status, priority, responses, and resolution.</p></article>
                    <article class="feature"><div class="feature-number">03</div><h3>Recommendations</h3><p>Community suggestions become visible, organized records for review and action.</p></article>
                    <article class="feature"><div class="feature-number">04</div><h3>Subdivision mapping</h3><p>Facilities and reported concerns are shown on a focused map of the Terra Nova area.</p></article>
                </div>
            </div>
        </section>

        <section class="trust-band">
            <div class="section-inner">
                <h2>Participation starts with a clear record.</h2>
                <p>SubSync is built for the selected subdivision community and is accessible through desktop and mobile browsers.</p>
            </div>
        </section>
    </main>

    <footer><strong>SubSync</strong><span>Terra Nova Homeowners Association &middot; Community management portal</span></footer>
</body>
</html>
