<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SubSync — Access Portal</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('styles.css') }}">
    <style>
        /* ── PAGE SPECIFIC OVERRIDES ── */
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            overflow-y: auto;
            padding: 20px 0;
        }

        /* Animated BG particles */
        .bg-orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.18;
            pointer-events: none;
            animation: orbFloat 8s ease-in-out infinite alternate;
        }
        .bg-orb-1 { width: 500px; height: 500px; background: #3b82f6; top: -10%; left: -10%; animation-delay: 0s; }
        .bg-orb-2 { width: 400px; height: 400px; background: #8b5cf6; bottom: -5%; right: -5%; animation-delay: -3s; }
        .bg-orb-3 { width: 300px; height: 300px; background: #06b6d4; top: 40%; left: 40%; animation-delay: -5s; }
        @keyframes orbFloat {
            from { transform: translate(0, 0) scale(1); }
            to   { transform: translate(30px, -30px) scale(1.08); }
        }

        /* ── PORTAL CARD ── */
        .portal-card {
            width: 440px;
            max-width: 95vw;
            background: rgba(255, 255, 255, 0.07);
            border: 1px solid rgba(255, 255, 255, 0.14);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-radius: 24px;
            padding: 44px 38px 36px;
            position: relative;
            animation: cardIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) both;
            overflow: hidden;
        }
        @keyframes cardIn {
            from { opacity: 0; transform: translateY(30px) scale(0.95); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        .card-shimmer {
            position: absolute;
            top: 0; left: -100%;
            width: 60%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.04), transparent);
            animation: shimmer 4s ease-in-out infinite;
            pointer-events: none;
        }
        @keyframes shimmer { 0%,100%{left:-100%} 50%{left:150%} }

        /* ── LOGO ── */
        .portal-logo {
            text-align: center;
            margin-bottom: 28px;
        }
        .portal-logo img {
            width: 72px;
            filter: drop-shadow(0 4px 16px rgba(59,130,246,0.4));
            animation: logoPulse 3s ease-in-out infinite;
        }
        @keyframes logoPulse {
            0%,100% { filter: drop-shadow(0 4px 16px rgba(59,130,246,0.3)); }
            50%      { filter: drop-shadow(0 4px 28px rgba(59,130,246,0.65)); }
        }
        .portal-logo h1 {
            font-size: 22px;
            font-weight: 700;
            margin-top: 10px;
            letter-spacing: 0.04em;
        }
        .portal-logo p {
            font-size: 12px;
            opacity: 0.45;
            letter-spacing: 0.05em;
            margin-top: 2px;
        }

        /* ── ROLE TABS ── */
        .role-tabs {
            display: flex;
            gap: 6px;
            background: rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 5px;
            margin-bottom: 26px;
        }
        .role-tab {
            flex: 1;
            padding: 9px 10px;
            border-radius: 9px;
            border: 1px solid transparent;
            background: transparent;
            color: rgba(255,255,255,0.5);
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.22s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-bottom: 0;
        }
        .role-tab:hover { color: rgba(255,255,255,0.85); background: rgba(255,255,255,0.07); }
        .role-tab.active {
            background: rgba(59,130,246,0.22);
            border-color: rgba(59,130,246,0.4);
            color: #93c5fd;
            font-weight: 600;
            box-shadow: 0 2px 12px rgba(59,130,246,0.18);
        }
        .role-tab.officer-active {
            background: rgba(139,92,246,0.22);
            border-color: rgba(139,92,246,0.4);
            color: #c4b5fd;
        }

        /* ── FORM SWITCH TABS (Login / Register) ── */
        .form-tabs {
            display: flex;
            gap: 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 22px;
        }
        .form-tab {
            flex: 1;
            padding: 9px;
            background: transparent;
            border: none;
            color: rgba(255,255,255,0.4);
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            margin-bottom: -1px;
            transition: all 0.2s;
            letter-spacing: 0.03em;
        }
        .form-tab:hover { color: rgba(255,255,255,0.75); background: transparent; }
        .form-tab.active { color: #fff; border-bottom-color: #3b82f6; font-weight: 600; background: transparent; }

        /* ── FORM PANELS ── */
        .form-panel { display: none; animation: fadeSlide 0.28s ease both; }
        .form-panel.active { display: block; }
        @keyframes fadeSlide {
            from { opacity: 0; transform: translateX(10px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        /* ── INPUTS ── */
        .portal-card input[type=email],
        .portal-card input[type=password],
        .portal-card input[type=text],
        .portal-card input[type=tel],
        .portal-card select {
            width: 100%;
            background: rgba(255,255,255,0.09);
            border: 1px solid rgba(255,255,255,0.16);
            color: white;
            padding: 12px 15px;
            border-radius: 11px;
            outline: none;
            transition: all 0.22s;
            font-size: 13.5px;
            font-family: "Segoe UI", sans-serif;
            margin-bottom: 11px;
        }
        .portal-card input:focus,
        .portal-card select:focus {
            background: rgba(0,0,0,0.45);
            border-color: rgba(59,130,246,0.55);
            box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        }
        .portal-card input::placeholder { color: rgba(255,255,255,0.3); }
        .portal-card select option { background: #111827; }

        .input-row {
            display: flex;
            gap: 10px;
        }
        .input-row input { flex: 1; }

        /* ── INPUT WITH ICON ── */
        .input-group { position: relative; margin-bottom: 11px; }
        .input-group input, .input-group select {
            margin-bottom: 0 !important;
            padding-left: 42px !important;
        }
        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
            pointer-events: none;
            opacity: 0.45;
        }

        /* ── SUBMIT BTN ── */
        .portal-btn {
            width: 100%;
            padding: 13px;
            border-radius: 11px;
            border: none;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s;
            margin-top: 6px;
            margin-bottom: 0;
            letter-spacing: 0.04em;
            position: relative;
            overflow: hidden;
        }
        .portal-btn::after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255,255,255,0);
            transition: background 0.2s;
        }
        .portal-btn:hover::after { background: rgba(255,255,255,0.08); }
        .portal-btn:active { transform: scale(0.98); }

        .btn-resident {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            box-shadow: 0 4px 20px rgba(59,130,246,0.35);
        }
        .btn-resident:hover { box-shadow: 0 6px 28px rgba(59,130,246,0.5); transform: translateY(-1px); }

        .btn-officer {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: white;
            box-shadow: 0 4px 20px rgba(139,92,246,0.35);
        }
        .btn-officer:hover { box-shadow: 0 6px 28px rgba(139,92,246,0.5); transform: translateY(-1px); }

        /* ── DIVIDER ── */
        .divider {
            display: flex; align-items: center; gap: 12px;
            font-size: 11px; opacity: 0.35;
            margin: 14px 0;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        .divider::before, .divider::after {
            content: ''; flex: 1;
            height: 1px; background: rgba(255,255,255,0.15);
        }

        /* ── ROLE BADGE ── */
        .role-badge {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 14px;
            border-radius: 11px;
            margin-bottom: 18px;
            font-size: 13px;
        }
        .role-badge-resident {
            background: rgba(59,130,246,0.12);
            border: 1px solid rgba(59,130,246,0.25);
            color: #93c5fd;
        }
        .role-badge-officer {
            background: rgba(139,92,246,0.12);
            border: 1px solid rgba(139,92,246,0.25);
            color: #c4b5fd;
        }
        .role-badge-icon { font-size: 20px; }
        .role-badge-text { font-weight: 600; }
        .role-badge-sub { font-size: 11px; opacity: 0.6; margin-top: 1px; }

        /* ── FOOTER LINK ── */
        .portal-footer {
            text-align: center;
            margin-top: 18px;
            font-size: 12px;
            opacity: 0.4;
        }
        .portal-footer a {
            color: #93c5fd;
            text-decoration: none;
            opacity: 1;
            font-weight: 600;
        }
        .portal-footer a:hover { text-decoration: underline; }

        /* ── ADMIN LINK ── */
        .admin-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 18px;
            padding: 9px;
            border-radius: 10px;
            font-size: 12px;
            color: rgba(255,255,255,0.4);
            text-decoration: none;
            border: 1px solid rgba(255,255,255,0.08);
            transition: all 0.2s;
        }
        .admin-link:hover {
            color: rgba(255,255,255,0.75);
            background: rgba(255,255,255,0.06);
            border-color: rgba(255,255,255,0.18);
        }

        /* ── ERROR / SUCCESS MSG ── */
        .form-msg {
            display: none;
            padding: 10px 13px;
            border-radius: 9px;
            font-size: 12px;
            margin-bottom: 12px;
            animation: fadeSlide 0.2s ease both;
        }
        .form-msg.error   { background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #fca5a5; }
        .form-msg.success { background: rgba(34,197,94,0.15); border: 1px solid rgba(34,197,94,0.3); color: #86efac; }
        .form-msg.show    { display: block; }

        /* ── PASSWORD EYE ── */
        .input-group .eye-btn {
            position: absolute;
            right: 13px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            color: rgba(255,255,255,0.35);
            cursor: pointer; font-size: 15px;
            padding: 0; margin: 0;
            transition: color 0.2s;
            width: auto;
        }
        .input-group .eye-btn:hover { color: rgba(255,255,255,0.7); background: none; box-shadow: none; }

        /* ── STRENGTH BAR ── */
        .strength-bar {
            height: 3px; border-radius: 3px;
            background: rgba(255,255,255,0.1);
            margin: -6px 0 12px;
            overflow: hidden;
        }
        .strength-fill {
            height: 100%; border-radius: 3px;
            width: 0; transition: width 0.3s, background 0.3s;
        }

        /* ── OFFICER DEPT ── */
        .officer-note {
            font-size: 11px;
            opacity: 0.45;
            text-align: center;
            margin-top: 10px;
            line-height: 1.5;
        }
    </style>
</head>
<body>

<!-- BG ORBS -->
<div class="bg-orb bg-orb-1"></div>
<div class="bg-orb bg-orb-2"></div>
<div class="bg-orb bg-orb-3"></div>

<div class="portal-card">
    <div class="card-shimmer"></div>

    <!-- LOGO -->
    <div class="portal-logo">
        <img src="emblem_white_1.svg" alt="SubSync">
        <h1>SubSync</h1>
        <p>TERRA NOVA SUBDIVISION PORTAL</p>
    </div>

    <!-- ROLE SELECTOR -->
    <div class="role-tabs">
        <button class="role-tab active" id="tab-resident" onclick="switchRole('resident')">
            🏠 Resident
        </button>
        <button class="role-tab" id="tab-officer" onclick="switchRole('officer')">
            🛡️ Officer
        </button>
    </div>

    <!-- ROLE BADGE (changes per role) -->
    <div class="role-badge role-badge-resident" id="roleBadge">
        <span class="role-badge-icon">🏠</span>
        <div>
            <div class="role-badge-text">Resident Access</div>
            <div class="role-badge-sub">Login or register your household account</div>
        </div>
    </div>

    <!-- FORM SWITCH TABS -->
    <div class="form-tabs" id="formTabs">
        <button class="form-tab active" id="loginTab" onclick="switchForm('login')">Sign In</button>
        <button class="form-tab" id="registerTab" onclick="switchForm('register')">Register</button>
    </div>

    <!-- MSG -->
    <div class="form-msg" id="formMsg"></div>

    <!-- ══ LOGIN FORM ══ -->
    <div class="form-panel active" id="loginPanel">
        <div class="input-group">
            <span class="input-icon">✉️</span>
            <input type="email" id="login-email" placeholder="Gmail Address" autocomplete="email">
        </div>
        <div class="input-group">
            <span class="input-icon">🔒</span>
            <input type="password" id="login-password" placeholder="Password" autocomplete="current-password">
            <button class="eye-btn" onclick="togglePwd('login-password',this)" type="button">👁</button>
        </div>

        <div style="text-align:right;margin:-4px 0 14px;">
            <a href="#" style="font-size:12px;color:rgba(255,255,255,0.45);text-decoration:none;" onclick="showMsg('Password reset link sent to your email!','success')">Forgot password?</a>
        </div>

        <button class="portal-btn btn-resident" id="loginBtn" onclick="handleLogin()">Sign In</button>

        <p class="officer-note" id="officerNote" style="display:none;">
            Officer access requires admin verification. Contact the HOA office if you need help logging in.
        </p>
    </div>

    <!-- ══ REGISTER FORM ══ -->
    <div class="form-panel" id="registerPanel">

        <!-- RESIDENT FIELDS -->
        <div id="resident-fields">
            <div class="input-group">
                <span class="input-icon">🏡</span>
                <input type="text" placeholder="Household / Family Name" required>
            </div>
            <div class="input-row">
                <div class="input-group" style="flex:1;margin-bottom:0;">
                    <span class="input-icon">📍</span>
                    <input type="text" placeholder="Block" style="padding-left:42px !important;">
                </div>
                <div class="input-group" style="flex:1;margin-bottom:0;">
                    <span class="input-icon">#</span>
                    <input type="text" placeholder="Lot">
                </div>
            </div>
            <div style="margin-bottom:11px;"></div>
            <div class="input-group">
                <span class="input-icon">✉️</span>
                <input type="email" placeholder="Gmail Address" required>
            </div>
            <div class="input-group">
                <span class="input-icon">📱</span>
                <input type="tel" placeholder="Contact Number" required>
            </div>
            <div class="input-group">
                <span class="input-icon">👤</span>
                <select required>
                    <option value="" disabled selected>Member Type</option>
                    <option value="head">Head of Household</option>
                    <option value="family">Family Member</option>
                    <option value="relative">Relative</option>
                    <option value="tenant">Tenant</option>
                </select>
            </div>
            <div class="input-group">
                <span class="input-icon">🔒</span>
                <input type="password" id="reg-password" placeholder="Create Password" oninput="updateStrength(this.value)" autocomplete="new-password">
                <button class="eye-btn" onclick="togglePwd('reg-password',this)" type="button">👁</button>
            </div>
            <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
            <div class="input-group">
                <span class="input-icon">🔑</span>
                <input type="password" placeholder="Confirm Password" autocomplete="new-password">
            </div>
        </div>

        <!-- OFFICER FIELDS (hidden by default) -->
        <div id="officer-fields" style="display:none;">
            <div class="input-group">
                <span class="input-icon">👤</span>
                <input type="text" placeholder="Full Name" required>
            </div>
            <div class="input-group">
                <span class="input-icon">🛡️</span>
                <select required>
                    <option value="" disabled selected>Officer Role</option>
                    <option>HOA President</option>
                    <option>HOA Vice President</option>
                    <option>Secretary</option>
                    <option>Treasurer</option>
                    <option>Block Representative</option>
                    <option>Security Officer</option>
                </select>
            </div>
            <div class="input-group">
                <span class="input-icon">📍</span>
                <select required>
                    <option value="" disabled selected>Assigned Block</option>
                    <option>Block 1</option><option>Block 2</option>
                    <option>Block 3</option><option>Block 4</option><option>Block 5</option>
                </select>
            </div>
            <div class="input-group">
                <span class="input-icon">✉️</span>
                <input type="email" placeholder="Gmail Address" required>
            </div>
            <div class="input-group">
                <span class="input-icon">🔒</span>
                <input type="password" id="off-password" placeholder="Create Password" oninput="updateStrength(this.value)" autocomplete="new-password">
                <button class="eye-btn" onclick="togglePwd('off-password',this)" type="button">👁</button>
            </div>
            <div class="strength-bar"><div class="strength-fill" id="strengthFill2"></div></div>
            <p style="font-size:11px;opacity:0.45;margin:-6px 0 12px;text-align:center;">Registration requires admin approval before access is granted.</p>
        </div>

        <button class="portal-btn btn-resident" id="registerBtn" onclick="handleRegister()">Create Account</button>
    </div>

    <!-- ADMIN LINK -->
    <a href="{{ route('admin.login') }}" class="admin-link">
        🔐 Admin Access Portal
    </a>
</div>

<script>
    let currentRole = 'resident';
    let currentForm = 'login';

    /* ── ROLE SWITCH ── */
    function switchRole(role) {
        currentRole = role;
        const isOfficer = role === 'officer';

        document.getElementById('tab-resident').className = 'role-tab' + (!isOfficer ? ' active' : '');
        document.getElementById('tab-officer').className  = 'role-tab' + (isOfficer  ? ' officer-active' : '');

        const badge = document.getElementById('roleBadge');
        badge.className = 'role-badge ' + (isOfficer ? 'role-badge-officer' : 'role-badge-resident');
        badge.innerHTML = isOfficer
            ? `<span class="role-badge-icon">🛡️</span><div><div class="role-badge-text">Officer Access</div><div class="role-badge-sub">HOA officer portal — credentials required</div></div>`
            : `<span class="role-badge-icon">🏠</span><div><div class="role-badge-text">Resident Access</div><div class="role-badge-sub">Login or register your household account</div></div>`;

        // Update button colors
        document.getElementById('loginBtn').className    = 'portal-btn ' + (isOfficer ? 'btn-officer' : 'btn-resident');
        document.getElementById('registerBtn').className = 'portal-btn ' + (isOfficer ? 'btn-officer' : 'btn-resident');

        // Show officer note on login
        document.getElementById('officerNote').style.display = isOfficer ? 'block' : 'none';

        // Toggle register fields
        if (currentForm === 'register') {
            document.getElementById('resident-fields').style.display = isOfficer ? 'none' : 'block';
            document.getElementById('officer-fields').style.display  = isOfficer ? 'block' : 'none';
        }

        clearMsg();
    }

    /* ── FORM SWITCH ── */
    function switchForm(form) {
        currentForm = form;
        const isRegister = form === 'register';

        document.getElementById('loginTab').classList.toggle('active', !isRegister);
        document.getElementById('registerTab').classList.toggle('active', isRegister);

        document.getElementById('loginPanel').classList.toggle('active', !isRegister);
        document.getElementById('registerPanel').classList.toggle('active', isRegister);

        if (isRegister) {
            const isOfficer = currentRole === 'officer';
            document.getElementById('resident-fields').style.display = isOfficer ? 'none' : 'block';
            document.getElementById('officer-fields').style.display  = isOfficer ? 'block' : 'none';
        }
        clearMsg();
    }

    /* ── HANDLERS ── */
    function handleLogin() {
        const email = document.getElementById('login-email').value.trim();
        const pwd   = document.getElementById('login-password').value;
        if (!email || !pwd) { showMsg('Please fill in all fields.', 'error'); return; }

        showMsg('Signing in…', 'success');

        fetch('{{ route("login.post") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({ email: email, password: pwd, role: currentRole }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showMsg('✅ Login successful! Redirecting…', 'success');
                setTimeout(() => { window.location.href = data.redirect; }, 1000);
            } else {
                showMsg(data.message || 'Invalid credentials.', 'error');
            }
        })
        .catch(() => {
            showMsg('Connection error. Please try again.', 'error');
        });
    }

    function handleRegister() {
        showMsg('✅ Account created! Awaiting admin verification.', 'success');
    }

    /* ── PASSWORD TOGGLE ── */
    function togglePwd(id, btn) {
        const inp = document.getElementById(id);
        if (!inp) return;
        const show = inp.type === 'password';
        inp.type = show ? 'text' : 'password';
        btn.textContent = show ? '🙈' : '👁';
    }

    /* ── STRENGTH BAR ── */
    function updateStrength(val) {
        const fills = document.querySelectorAll('.strength-fill');
        let score = 0;
        if (val.length >= 8) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^a-zA-Z0-9]/.test(val)) score++;
        const pct   = (score / 4) * 100;
        const color = score <= 1 ? '#f87171' : score === 2 ? '#facc15' : score === 3 ? '#60a5fa' : '#22c55e';
        fills.forEach(f => { f.style.width = pct + '%'; f.style.background = color; });
    }

    /* ── MESSAGES ── */
    function showMsg(text, type) {
        const el = document.getElementById('formMsg');
        el.textContent = text;
        el.className = 'form-msg ' + type + ' show';
    }
    function clearMsg() {
        const el = document.getElementById('formMsg');
        el.className = 'form-msg';
    }
</script>
</body>
</html>
