<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SubSync — Admin Access</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('styles.css') }}">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            overflow-y: auto;
            padding: 20px 0;
        }

        /* Dark, more serious background for admin */
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.78);
            z-index: -1;
        }

        /* Subtle security-grid pattern */
        .admin-grid-bg {
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(59,130,246,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(59,130,246,0.04) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
            z-index: -1;
            animation: gridPan 20s linear infinite;
        }
        @keyframes gridPan { from{transform:translate(0,0)} to{transform:translate(40px,40px)} }

        /* Subtle corner glows */
        .corner-glow {
            position: fixed;
            width: 400px; height: 400px;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.1;
            pointer-events: none;
        }
        .corner-glow-tl { top:-100px; left:-100px; background: #3b82f6; }
        .corner-glow-br { bottom:-100px; right:-100px; background: #1e40af; }

        /* ── ADMIN CARD ── */
        .admin-card {
            width: 400px;
            max-width: 94vw;
            background: rgba(10, 12, 22, 0.88);
            border: 1px solid rgba(255,255,255,0.1);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border-radius: 22px;
            padding: 48px 38px 38px;
            position: relative;
            animation: cardIn 0.45s cubic-bezier(0.34, 1.4, 0.64, 1) both;
        }
        @keyframes cardIn {
            from { opacity:0; transform:translateY(24px) scale(0.96); }
            to   { opacity:1; transform:translateY(0) scale(1); }
        }

        /* Top accent line */
        .admin-card::before {
            content: '';
            position: absolute;
            top: 0; left: 20%; right: 20%;
            height: 2px;
            background: linear-gradient(90deg, transparent, #3b82f6, transparent);
            border-radius: 2px;
        }

        /* ── HEADER ── */
        .admin-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .admin-emblem {
            width: 68px; height: 68px;
            border-radius: 16px;
            background: rgba(59,130,246,0.12);
            border: 1px solid rgba(59,130,246,0.3);
            display: flex; align-items: center; justify-content: center;
            font-size: 30px;
            margin: 0 auto 16px;
            position: relative;
            box-shadow: 0 0 30px rgba(59,130,246,0.15), inset 0 1px 0 rgba(255,255,255,0.08);
        }
        .admin-emblem img {
            width: 44px;
            filter: drop-shadow(0 2px 8px rgba(59,130,246,0.5));
        }
        /* Pulsing ring */
        .admin-emblem::after {
            content: '';
            position: absolute;
            inset: -6px;
            border-radius: 22px;
            border: 1px solid rgba(59,130,246,0.2);
            animation: ringPulse 2.5s ease-in-out infinite;
        }
        @keyframes ringPulse {
            0%,100% { opacity:0.3; transform:scale(1); }
            50%      { opacity:0.8; transform:scale(1.04); }
        }

        .admin-header h1 { font-size: 20px; font-weight: 700; letter-spacing: 0.04em; margin-bottom: 4px; }
        .admin-header p  { font-size: 12px; color: rgba(255,255,255,0.35); letter-spacing: 0.06em; text-transform: uppercase; }

        /* ── SECURE BADGE ── */
        .secure-badge {
            display: flex; align-items: center; gap: 8px;
            background: rgba(34,197,94,0.09);
            border: 1px solid rgba(34,197,94,0.2);
            border-radius: 9px;
            padding: 9px 13px;
            font-size: 12px;
            color: #86efac;
            margin-bottom: 22px;
        }
        .secure-dot {
            width: 7px; height: 7px; border-radius: 50%;
            background: #22c55e;
            animation: securePulse 2s ease-in-out infinite;
            flex-shrink: 0;
        }
        @keyframes securePulse { 0%,100%{opacity:1;box-shadow:0 0 0 0 rgba(34,197,94,0.4)} 50%{opacity:0.7;box-shadow:0 0 0 4px rgba(34,197,94,0)} }

        /* ── INPUTS ── */
        .input-group {
            position: relative;
            margin-bottom: 14px;
        }
        .input-group input {
            width: 100%;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.12);
            color: white;
            padding: 13px 16px 13px 44px;
            border-radius: 11px;
            outline: none;
            font-size: 14px;
            font-family: "Segoe UI", sans-serif;
            transition: all 0.22s;
            letter-spacing: 0.02em;
            margin-bottom: 0;
        }
        .input-group input:focus {
            background: rgba(0,0,0,0.5);
            border-color: rgba(59,130,246,0.5);
            box-shadow: 0 0 0 3px rgba(59,130,246,0.08);
        }
        .input-group input::placeholder { color: rgba(255,255,255,0.25); font-size: 13px; }
        .input-icon {
            position: absolute; left: 15px; top: 50%;
            transform: translateY(-50%);
            font-size: 16px; opacity: 0.35; pointer-events: none;
        }
        .eye-btn {
            position: absolute; right: 14px; top: 50%;
            transform: translateY(-50%);
            background: none !important; border: none !important;
            color: rgba(255,255,255,0.3) !important; cursor: pointer;
            font-size: 15px; padding: 0 !important;
            margin: 0 !important; width: auto !important;
            transition: color 0.2s;
        }
        .eye-btn:hover { color: rgba(255,255,255,0.65) !important; background: none !important; box-shadow: none !important; }

        /* ── SUBMIT ── */
        .admin-btn {
            width: 100%;
            padding: 14px;
            border-radius: 11px;
            border: none;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s;
            letter-spacing: 0.05em;
            margin-top: 4px;
            box-shadow: 0 4px 20px rgba(59,130,246,0.3);
            position: relative;
            overflow: hidden;
        }
        .admin-btn::after { content:''; position:absolute; inset:0; background:rgba(255,255,255,0); transition:background 0.2s; }
        .admin-btn:hover::after { background:rgba(255,255,255,0.08); }
        .admin-btn:hover { transform:translateY(-1px); box-shadow:0 6px 28px rgba(59,130,246,0.45); }
        .admin-btn:active { transform:scale(0.98); }

        /* Loading state */
        .admin-btn.loading { pointer-events:none; }
        .admin-btn .btn-text { transition: opacity 0.2s; }
        .admin-btn .btn-spinner { display:none; }
        .admin-btn.loading .btn-text { opacity:0; }
        .admin-btn.loading .btn-spinner {
            display:block; position:absolute; top:50%; left:50%;
            transform:translate(-50%,-50%);
            width:20px; height:20px;
            border:2px solid rgba(255,255,255,0.3);
            border-top-color:#fff; border-radius:50%;
            animation:spin 0.7s linear infinite;
        }
        @keyframes spin { to{transform:translate(-50%,-50%) rotate(360deg)} }

        /* ── MSG ── */
        .form-msg {
            display:none; padding:11px 14px;
            border-radius:9px; font-size:12.5px;
            margin-bottom:14px; animation:msgIn 0.2s ease both;
        }
        @keyframes msgIn { from{opacity:0;transform:translateY(-4px)} to{opacity:1;transform:none} }
        .form-msg.error   { background:rgba(239,68,68,0.14); border:1px solid rgba(239,68,68,0.3); color:#fca5a5; display:block; }
        .form-msg.success { background:rgba(34,197,94,0.14); border:1px solid rgba(34,197,94,0.3); color:#86efac; display:block; }

        /* ── FOOTER ── */
        .admin-footer {
            text-align:center; margin-top:22px;
            display:flex; flex-direction:column; gap:10px;
        }
        .back-link {
            display:inline-flex; align-items:center; justify-content:center; gap:6px;
            font-size:12px; color:rgba(255,255,255,0.35);
            text-decoration:none; transition:color 0.2s;
        }
        .back-link:hover { color:rgba(255,255,255,0.65); }
        .legal-note {
            font-size:11px; color:rgba(255,255,255,0.2);
            line-height:1.5;
        }

        /* ── ATTEMPT COUNTER ── */
        .attempt-warning {
            display:none;
            text-align:center; font-size:11px;
            color:#fca5a5; margin-top:8px;
        }
    </style>
</head>
<body>

<div class="admin-grid-bg"></div>
<div class="corner-glow corner-glow-tl"></div>
<div class="corner-glow corner-glow-br"></div>

<div class="admin-card">

    <!-- HEADER -->
    <div class="admin-header">
        <div class="admin-emblem">
            <img src="emblem_white_1.svg" alt="SubSync">
        </div>
        <h1>Admin Access</h1>
        <p>Authorized Personnel Only</p>
    </div>

    <!-- SECURE BADGE -->
    <div class="secure-badge">
        <div class="secure-dot"></div>
        <span>Secure connection · Terra Nova Admin Portal</span>
    </div>

    <!-- MESSAGE -->
    <div class="form-msg" id="formMsg"></div>

    <!-- FORM -->
    <div class="input-group">
        <span class="input-icon">✉️</span>
        <input type="email" id="admin-email" placeholder="Admin Gmail Address" autocomplete="email"
            onkeydown="if(event.key==='Enter')focusNext('admin-password')">
    </div>

    <div class="input-group">
        <span class="input-icon">🔒</span>
        <input type="password" id="admin-password" placeholder="Password" autocomplete="current-password"
            onkeydown="if(event.key==='Enter')handleAdminLogin()">
        <button class="eye-btn" onclick="togglePwd()" type="button">👁</button>
    </div>

    <div style="text-align:right; margin:-6px 0 16px;">
        <a href="#" style="font-size:12px;color:rgba(255,255,255,0.3);text-decoration:none;"
           onclick="showMsg('Contact the system administrator to reset your password.','error');return false;">
            Forgot password?
        </a>
    </div>

    <button class="admin-btn" id="loginBtn" onclick="handleAdminLogin()">
        <span class="btn-text">Access Admin Panel</span>
        <span class="btn-spinner"></span>
    </button>

    <div class="attempt-warning" id="attemptWarn"></div>

    <!-- FOOTER -->
    <div class="admin-footer">
        <a href="{{ route('login') }}" class="back-link">← Back to Resident / Officer Portal</a>
        <p class="legal-note">Unauthorized access is strictly prohibited.<br>All login attempts are logged and monitored.</p>
    </div>

</div>

<script>
    let attempts = 0;
    const MAX_ATTEMPTS = 5;

    function togglePwd() {
        const inp = document.getElementById('admin-password');
        const btn = document.querySelector('.eye-btn');
        const show = inp.type === 'password';
        inp.type = show ? 'text' : 'password';
        btn.textContent = show ? '🙈' : '👁';
    }

    function focusNext(id) {
        document.getElementById(id).focus();
    }

    function showMsg(text, type) {
        const el = document.getElementById('formMsg');
        el.textContent = text;
        el.className = 'form-msg ' + type;
    }

    function handleAdminLogin() {
        const email = document.getElementById('admin-email').value.trim();
        const pwd   = document.getElementById('admin-password').value;
        const btn   = document.getElementById('loginBtn');
        const warn  = document.getElementById('attemptWarn');

        if (!email || !pwd) {
            showMsg('Please enter your email and password.', 'error');
            return;
        }

        attempts++;

        if (attempts >= MAX_ATTEMPTS) {
            showMsg('Too many failed attempts. Access temporarily locked.', 'error');
            btn.disabled = true;
            btn.style.opacity = '0.5';
            warn.style.display = 'none';
            return;
        }

        btn.classList.add('loading');

        fetch('{{ route("admin.login.post") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({ email: email, password: pwd }),
        })
        .then(r => r.json())
        .then(data => {
            btn.classList.remove('loading');
            if (data.success) {
                showMsg('✅ Access granted. Redirecting to dashboard…', 'success');
                setTimeout(() => { window.location.href = data.redirect; }, 1000);
            } else {
                const remaining = MAX_ATTEMPTS - attempts;
                showMsg(data.message || `Invalid credentials. ${remaining} attempt${remaining !== 1 ? 's' : ''} remaining.`, 'error');
                if (remaining <= 2) {
                    warn.textContent = '⚠️ Your account will be locked after too many failed attempts.';
                    warn.style.display = 'block';
                }
            }
        })
        .catch(() => {
            btn.classList.remove('loading');
            showMsg('Connection error. Please try again.', 'error');
        });
    }
</script>
</body>
</html>
