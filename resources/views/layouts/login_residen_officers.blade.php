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
            background: #FAF6F0;
            color: #2B2927;
        }

        /* Animated BG particles (hidden in minimalist style) */
        .bg-orb {
            display: none;
        }

        /* ── PORTAL CARD ── */
        .portal-card {
            width: 440px;
            max-width: 95vw;
            background: #FFFFFF;
            border: 1px solid #E6DFD5;
            box-shadow: 0 10px 30px rgba(43, 41, 39, 0.04);
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
            display: none;
        }

        /* ── LOGO ── */
        .portal-logo {
            text-align: center;
            margin-bottom: 28px;
        }
        .portal-logo img {
            width: 72px;
            filter: none;
        }
        .portal-logo h1 {
            font-size: 22px;
            font-weight: 700;
            margin-top: 10px;
            letter-spacing: 0.04em;
            color: #2B2927;
        }
        .portal-logo p {
            font-size: 12px;
            opacity: 0.6;
            color: #7D7975;
            letter-spacing: 0.05em;
            margin-top: 2px;
        }

        /* ── ROLE TABS ── */
        .role-tabs {
            display: flex;
            gap: 6px;
            background: #FAF6F0;
            border: 1px solid #E6DFD5;
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
            color: #7D7975;
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
        .role-tab:hover { color: #2B2927; background: rgba(0,0,0,0.02); }
        .role-tab.active {
            background: #FFFFFF;
            border-color: #E6DFD5;
            color: #A07D53;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(43,41,39,0.04);
        }
        .role-tab.officer-active {
            background: #FFFFFF;
            border-color: #E6DFD5;
            color: #A07D53;
        }

        /* ── FORM SWITCH TABS (Login / Register) ── */
        .form-tabs {
            display: flex;
            gap: 0;
            border-bottom: 1px solid #E6DFD5;
            margin-bottom: 22px;
        }
        .form-tab {
            flex: 1;
            padding: 9px;
            background: transparent;
            border: none;
            color: #7D7975;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            margin-bottom: -1px;
            transition: all 0.2s;
            letter-spacing: 0.03em;
        }
        .form-tab:hover { color: #2B2927; background: transparent; }
        .form-tab.active { color: #A07D53; border-bottom-color: #A07D53; font-weight: 600; background: transparent; }

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
            background: #FAF6F0;
            border: 1px solid #E6DFD5;
            color: #2B2927;
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
            background: #FFFFFF;
            border-color: #A07D53;
            box-shadow: 0 0 0 3px rgba(160,125,83,0.08);
        }
        .portal-card input::placeholder { color: #9C9790; }
        .portal-card select option { background: #FFFFFF; color: #2B2927; }

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
            background: #2B2927;
            color: #FFFFFF;
            box-shadow: none;
        }
        .btn-resident:hover { background: #4A4744; transform: translateY(-1px); }

        .btn-officer {
            background: #A07D53;
            color: #FFFFFF;
            box-shadow: none;
        }
        .btn-officer:hover { background: #B6966E; transform: translateY(-1px); }

        /* ── DIVIDER ── */
        .divider {
            display: flex; align-items: center; gap: 12px;
            font-size: 11px; opacity: 0.5; color: #7D7975;
            margin: 14px 0;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        .divider::before, .divider::after {
            content: ''; flex: 1;
            height: 1px; background: #E6DFD5;
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
            background: #FAF6F0;
            border: 1px solid #E6DFD5;
            color: #2B2927;
        }
        .role-badge-officer {
            background: #FAF3E5;
            border: 1px solid #E6DFD5;
            color: #A07D53;
        }
        .role-badge-icon { font-size: 20px; }
        .role-badge-text { font-weight: 600; }
        .role-badge-sub { font-size: 11px; opacity: 0.7; margin-top: 1px; }

        /* ── FOOTER LINK ── */
        .portal-footer {
            text-align: center;
            margin-top: 18px;
            font-size: 12px;
            opacity: 0.6;
            color: #7D7975;
        }
        .portal-footer a {
            color: #A07D53;
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
            color: #7D7975;
            text-decoration: none;
            border: 1px solid #E6DFD5;
            transition: all 0.2s;
        }
        .admin-link:hover {
            color: #2B2927;
            background: #FAF6F0;
            border-color: #D6CFC5;
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
        .form-msg.error   { background: #F9ECEC; border: 1px solid rgba(189,91,91,0.2); color: #BD5B5B; }
        .form-msg.success { background: #EAF2EC; border: 1px solid rgba(107,142,112,0.2); color: #6B8E70; }
        .form-msg.show    { display: block; }

        /* ── PASSWORD EYE ── */
        .input-group .eye-btn {
            position: absolute;
            right: 13px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            color: #7D7975;
            cursor: pointer; font-size: 15px;
            padding: 0; margin: 0;
            transition: color 0.2s;
            width: auto;
        }
        .input-group .eye-btn:hover { color: #2B2927; background: none; box-shadow: none; }

        /* ── STRENGTH BAR ── */
        .strength-bar {
            height: 3px; border-radius: 3px;
            background: #FAF6F0;
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
            opacity: 0.6;
            color: #7D7975;
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
        <img src="{{ asset('emblem_1.svg') }}" alt="SubSync">
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
                <input type="text" id="reg-family-name" placeholder="Full Name" required>
            </div>
            <div class="input-row">
                <div class="input-group" style="flex:1;margin-bottom:0;">
                    <span class="input-icon">📍</span>
                    <input type="text" id="reg-block" placeholder="Block" style="padding-left:42px !important;">
                </div>
                <div class="input-group" style="flex:1;margin-bottom:0;">
                    <span class="input-icon">#</span>
                    <input type="text" id="reg-lot" placeholder="Lot">
                </div>
            </div>
            <div style="margin-bottom:11px;"></div>
            <div class="input-group">
                <span class="input-icon">✉️</span>
                <input type="email" id="reg-email" placeholder="Gmail Address" required>
            </div>
            <div class="input-group">
                <span class="input-icon">📱</span>
                <input type="tel" id="reg-contact" placeholder="Contact Number" required>
            </div>
            <div class="input-group">
                <span class="input-icon">👤</span>
                <select id="reg-member-type" required>
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
                <input type="password" id="reg-confirm" placeholder="Confirm Password" autocomplete="new-password">
            </div>
        </div>

        <!-- OFFICER FIELDS (hidden by default) -->
        <div id="officer-fields" style="display:none;">
            <div class="input-group">
                <span class="input-icon">👤</span>
                <input type="text" id="off-name" placeholder="Full Name" required>
            </div>
            <div class="input-group">
                <span class="input-icon">🛡️</span>
                <select id="off-role" required>
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
                <select id="off-block" required>
                    <option value="" disabled selected>Assigned Block</option>
                    <option>Block 1</option><option>Block 2</option>
                    <option>Block 3</option><option>Block 4</option><option>Block 5</option>
                </select>
            </div>
            <div class="input-group">
                <span class="input-icon">✉️</span>
                <input type="email" id="off-email" placeholder="Gmail Address" required>
            </div>
            <div class="input-group">
                <span class="input-icon">🔒</span>
                <input type="password" id="off-password" placeholder="Create Password" oninput="updateStrength(this.value)" autocomplete="new-password">
                <button class="eye-btn" onclick="togglePwd('off-password',this)" type="button">👁</button>
            </div>
            <div class="strength-bar"><div class="strength-fill" id="strengthFill2"></div></div>
            <div class="input-group">
                <span class="input-icon">🔑</span>
                <input type="password" id="off-confirm" placeholder="Confirm Password" autocomplete="new-password">
            </div>
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
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const btn  = document.getElementById('registerBtn');

        if (currentRole === 'officer') {
            const name     = document.getElementById('off-name')?.value.trim();
            const role     = document.getElementById('off-role')?.value;
            const email    = document.getElementById('off-email')?.value.trim();
            const password = document.getElementById('off-password')?.value;
            const confirm  = document.getElementById('off-confirm')?.value;
            if (!name || !email || !password) { showMsg('Please fill in all required fields.', 'error'); return; }
            if (password !== confirm) { showMsg('Passwords do not match.', 'error'); return; }
            if (password.length < 8)  { showMsg('Password must be at least 8 characters.', 'error'); return; }

            btn.disabled = true;
            showMsg('Submitting…', 'success');
            fetch('{{ route("register.post") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ role: 'officer', name, email, password, role_description: role }),
            })
            .then(r => r.json())
            .then(data => {
                btn.disabled = false;
                if (data.success) {
                    showMsg('✅ Application submitted! An admin will review and approve your account.', 'success');
                    setTimeout(() => switchForm('login'), 3000);
                } else {
                    const errs = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Registration failed.');
                    showMsg(errs, 'error');
                }
            })
            .catch(() => { btn.disabled = false; showMsg('Connection error. Please try again.', 'error'); });
        } else {
            const name     = document.getElementById('reg-family-name')?.value.trim();
            const email    = document.getElementById('reg-email')?.value.trim();
            const contact  = document.getElementById('reg-contact')?.value.trim();
            const password = document.getElementById('reg-password')?.value;
            const confirm  = document.getElementById('reg-confirm')?.value;
            if (!name || !email || !password) { showMsg('Please fill in all required fields.', 'error'); return; }
            if (password !== confirm) { showMsg('Passwords do not match.', 'error'); return; }
            if (password.length < 8)  { showMsg('Password must be at least 8 characters.', 'error'); return; }

            btn.disabled = true;
            showMsg('Submitting…', 'success');
            fetch('{{ route("register.post") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ role: 'resident', name, email, password, contact_number: contact || null }),
            })
            .then(r => r.json())
            .then(data => {
                btn.disabled = false;
                if (data.success) {
                    showMsg('✅ Account created! Awaiting admin approval — you will be able to log in once approved.', 'success');
                    setTimeout(() => switchForm('login'), 4000);
                } else {
                    const errs = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Registration failed.');
                    showMsg(errs, 'error');
                }
            })
            .catch(() => { btn.disabled = false; showMsg('Connection error. Please try again.', 'error'); });
        }
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
