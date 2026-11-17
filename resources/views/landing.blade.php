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
            --ink: #1C1917;
            --muted: #716B64;
            --paper: #FAF6F0;
            --panel: #FFFFFF;
            --line: #E8E2D9;
            --accent: #FA7D09;
            --accent-hover: #E56F05;
            --accent-soft: #FFF2E5;
            --accent-border: #FCD5B5;
            --blue: #3E6A8F;
            --green: #4F7A57;
        }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { margin: 0; background: var(--paper); color: var(--ink); font-family: 'DM Sans', sans-serif; }
        a { color: inherit; text-decoration: none; }
        .site-header-bar {
            background: #FFFFFF;
            width: 100%;
            border-bottom: 1px solid rgba(232, 226, 217, 0.7);
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 3px rgba(28, 25, 23, 0.03);
        }
        .site-header { max-width: 1180px; margin: 0 auto; padding: 18px 28px; display: flex; align-items: center; justify-content: space-between; gap: 24px; background: #FFFFFF; }
        .brand { display: flex; align-items: center; gap: 12px; font-weight: 700; letter-spacing: .04em; }
        .brand img { width: 44px; height: 44px; object-fit: contain; }
        .brand small { display: block; color: var(--muted); font-size: 10px; font-weight: 600; letter-spacing: .12em; text-transform: uppercase; margin-top: 3px; }
        .header-nav { display: flex; align-items: center; gap: 20px; font-size: 13.5px; color: var(--muted); font-weight: 500; }
        .header-nav a:hover { color: var(--ink); }
        .header-login {
            background: #F58B1F;
            border: 1px solid #F58B1F;
            border-radius: 999px;
            padding: 9px 20px;
            color: #FFFFFF;
            font-weight: 700;
            font-size: 13.5px;
            letter-spacing: .01em;
            box-shadow: 0 3px 10px rgba(245, 139, 31, 0.28);
            transition: all .18s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .header-login:hover {
            background: #E57D12;
            border-color: #E57D12;
            color: #FFFFFF;
            box-shadow: 0 5px 16px rgba(245, 139, 31, 0.40);
            transform: translateY(-1px);
        }
        .header-login:active {
            background: #D46F08;
            transform: scale(0.98);
        }
        .hero-wrap {
            background-color: #FAF6F0;
            background-image:
                linear-gradient(90deg, #FAF6F0 0%, #FAF6F0 42%, rgba(250, 246, 240, 0.92) 52%, rgba(250, 246, 240, 0.4) 62%, transparent 72%),
                url('{{ asset("images/terranova_hero_bg.jpg") }}?v={{ file_exists(public_path("images/terranova_hero_bg.jpg")) ? filemtime(public_path("images/terranova_hero_bg.jpg")) : 2 }}');
            background-size: cover;
            background-position: center right;
            background-repeat: no-repeat;
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid var(--line);
        }
        .hero { max-width: 1180px; margin: 0 auto; padding: 76px 28px 96px; display: grid; grid-template-columns: minmax(0, 1.15fr) minmax(300px, .85fr); gap: 72px; align-items: center; position: relative; z-index: 2; }
        .eyebrow { color: var(--accent); font-size: 11px; font-weight: 700; letter-spacing: .15em; text-transform: uppercase; margin-bottom: 18px; }
        .eyebrow-accent { display: inline-flex; align-items: center; gap: 12px; }
        .eyebrow-accent::after { content: ''; display: inline-block; width: 30px; height: 3px; background: var(--accent); border-radius: 2px; }
        h1, h2 { font-family: 'DM Serif Display', Georgia, serif; font-weight: 400; }
        h1 { font-size: clamp(42px, 5.5vw, 74px); line-height: 1.04; letter-spacing: .01em; margin: 0 0 22px; max-width: 720px; color: var(--ink); }
        .hero-copy { max-width: 620px; color: var(--muted); font-size: 16.5px; line-height: 1.75; margin: 0 0 32px; }
        .actions { display: flex; flex-wrap: wrap; gap: 12px; }
        .button {
            display: inline-flex; align-items: center; justify-content: center;
            min-height: 46px; padding: 0 24px;
            border: none; border-radius: 10px;
            background: #FA7D09;
            color: #FFFFFF; font-size: 14.5px; font-weight: 700;
            box-shadow: 0 4px 14px rgba(250, 125, 9, 0.32);
            transition: all 0.2s ease; cursor: pointer;
        }
        .button:hover {
            background: #E56F05; color: #FFFFFF;
            transform: translateY(-1px); box-shadow: 0 6px 18px rgba(250, 125, 9, 0.42);
        }
        .button:active {
            background: #D46603; transform: scale(0.99);
        }
        .button.secondary {
            background: #FFFFFF; border: 1.5px solid var(--line); color: var(--ink); box-shadow: none;
        }
        .button.secondary:hover { border-color: var(--accent); color: var(--accent); background: #FFF9F2; }
        .hero-panel { background: var(--panel); border: 1px solid var(--line); border-radius: 16px; padding: 28px; box-shadow: 0 16px 40px rgba(43, 41, 39, .06); }
        .panel-label { color: var(--muted); font-size: 10.5px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; margin-bottom: 20px; }
        .portal-row { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 16px 0; border-top: 1px solid var(--line); transition: color 0.18s; }
        .portal-row:hover strong { color: var(--accent); }
        .portal-row strong { display: block; font-size: 14.5px; color: var(--ink); transition: color .18s; }
        .portal-row span { display: block; color: var(--muted); font-size: 12.5px; margin-top: 4px; line-height: 1.5; }
        .portal-arrow { color: var(--accent); font-size: 20px; transition: transform 0.18s; }
        .portal-row:hover .portal-arrow { transform: translateX(3px); }
        .section { border-top: 1px solid var(--line); }
        .section-inner { max-width: 1180px; margin: 0 auto; padding: 76px 28px; }
        .section-heading { max-width: 640px; margin-bottom: 36px; }
        h2 { font-size: 38px; line-height: 1.15; margin: 0 0 12px; color: var(--ink); }
        .section-heading p { color: var(--muted); line-height: 1.7; margin: 0; font-size: 15px; }
        .feature-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
        .feature { background: var(--panel); border: 1px solid var(--line); border-radius: 14px; padding: 24px; min-height: 200px; box-shadow: 0 4px 16px rgba(43,41,39,0.03); transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s; }
        .feature:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(43,41,39,0.07); border-color: var(--accent-border); }
        .feature-head { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
        .feature-icon { width: 42px; height: 42px; border-radius: 11px; background: var(--accent-soft); color: var(--accent); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .feature-number { color: var(--accent); font-family: 'DM Serif Display', Georgia, serif; font-size: 26px; line-height: 1; }
        .feature h3 { font-size: 15.5px; margin: 0 0 8px; font-weight: 700; color: var(--ink); }
        .feature p { color: var(--muted); font-size: 13px; line-height: 1.65; margin: 0; }
        .trust-band { background: #1C1917; color: #fff; }
        .trust-band .section-inner { display: flex; align-items: center; justify-content: space-between; gap: 36px; }
        .trust-band h2 { margin-bottom: 0; color: #fff; font-size: 34px; line-height: 1.18; }
        .trust-band-bar { width: 62px; height: 3px; background: var(--accent); border-radius: 2px; margin-top: 14px; }
        .trust-band p { color: #D2CBC1; font-size: 13.5px; line-height: 1.65; max-width: 480px; margin: 0 0 18px; }
        .trust-badges { display: flex; gap: 12px; flex-wrap: wrap; }
        .access-badge { display: inline-flex; align-items: center; gap: 10px; padding: 9px 16px; border-radius: 10px; background: #262320; border: 1px solid rgba(255,255,255,0.12); color: #FFFFFF; font-size: 12.5px; font-weight: 600; }
        .access-badge svg { color: var(--accent); width: 18px; height: 18px; flex-shrink: 0; }
        footer { max-width: 1180px; margin: 0 auto; padding: 26px 28px 40px; color: var(--muted); font-size: 11.5px; display: flex; justify-content: space-between; gap: 16px; }
        @media (max-width: 800px) {
            .header-nav a:not(.header-login) { display: none; }
            .hero-wrap {
                background-image:
                    linear-gradient(180deg, rgba(250, 246, 240, 0.96) 0%, rgba(250, 246, 240, 0.90) 65%, rgba(250, 246, 240, 0.80) 100%),
                    url('{{ asset("images/terranova_hero_bg.jpg") }}?v={{ file_exists(public_path("images/terranova_hero_bg.jpg")) ? filemtime(public_path("images/terranova_hero_bg.jpg")) : 2 }}');
            }
            .hero { grid-template-columns: 1fr; gap: 36px; padding-top: 48px; }
            .feature-grid { grid-template-columns: 1fr 1fr; }
            .trust-band .section-inner { display: block; }
            .trust-band p { margin-top: 16px; }
        }
        @media (max-width: 500px) {
            .site-header, .hero, .section-inner, footer { padding-left: 18px; padding-right: 18px; }
            h1 { font-size: 42px; }
            .feature-grid { grid-template-columns: 1fr; }
            footer { display: block; }
            footer span { display: block; margin-top: 6px; }
        }
    </style>
</head>
<body>
    <div class="site-header-bar">
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
    </div>

    <main>
        <div class="hero-wrap">
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
        </div>

        <section class="section" id="capabilities">
            <div class="section-inner">
                <div class="section-heading">
                    <div class="eyebrow eyebrow-accent">One shared community record</div>
                    <h2>Designed around the work Terra Nova needs to do.</h2>
                    <p>SubSync replaces scattered notices and informal follow-up with structured, accessible workflows.</p>
                </div>
                <div class="feature-grid">
                    <article class="feature">
                        <div class="feature-head">
                            <div class="feature-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></svg>
                            </div>
                            <div class="feature-number">01</div>
                        </div>
                        <h3>Communication</h3>
                        <p>Direct correspondence and official announcements keep residents and administrators aligned.</p>
                    </article>
                    <article class="feature">
                        <div class="feature-head">
                            <div class="feature-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            </div>
                            <div class="feature-number">02</div>
                        </div>
                        <h3>Issue reporting</h3>
                        <p>Residents can document concerns while administrators track status, priority, responses, and resolution.</p>
                    </article>
                    <article class="feature">
                        <div class="feature-head">
                            <div class="feature-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="M9 14l2 2 4-4"/></svg>
                            </div>
                            <div class="feature-number">03</div>
                        </div>
                        <h3>Recommendations</h3>
                        <p>Community suggestions become visible, organized records for review and action.</p>
                    </article>
                    <article class="feature">
                        <div class="feature-head">
                            <div class="feature-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/><line x1="9" y1="3" x2="9" y2="18"/><line x1="15" y1="6" x2="15" y2="21"/></svg>
                            </div>
                            <div class="feature-number">04</div>
                        </div>
                        <h3>Subdivision mapping</h3>
                        <p>Facilities and reported concerns are shown on a focused map of the Terra Nova area.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="trust-band">
            <div class="section-inner">
                <div>
                    <h2>Participation starts with a clear record.</h2>
                    <div class="trust-band-bar"></div>
                </div>
                <div>
                    <p>SubSync is built for the selected subdivision community and is accessible through desktop and mobile browsers.</p>
                    <div class="trust-badges">
                        <div class="access-badge">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                            Desktop Access
                        </div>
                        <div class="access-badge">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                            Mobile Access
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer><strong>SubSync</strong><span>Terra Nova Homeowners Association &middot; Community management portal</span></footer>
</body>
</html>