<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SubSync — Complete Registration</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet">
    <style>
        :root { --accent:#A07D53; --text:#2B2927; --dim:#7D7975; --border:#E6DFD5; }
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
            background: #FAF6F0; color: var(--text);
            font-family: 'Inter', -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            padding: 24px;
        }
        .card {
            width: 100%; max-width: 460px; background: #fff; border: 1px solid var(--border);
            border-radius: 14px; padding: 34px 32px; box-shadow: 0 12px 40px rgba(43,41,39,0.08);
        }
        .brand { font-family: 'DM Serif Display', serif; font-size: 24px; margin-bottom: 2px; }
        .sub { font-size: 13px; color: var(--dim); margin-bottom: 22px; }
        .who { display: flex; align-items: center; gap: 12px; padding: 12px; border: 1px solid var(--border); border-radius: 10px; margin-bottom: 22px; background: #FAF6F0; }
        .who img { width: 46px; height: 46px; border-radius: 50%; object-fit: cover; border: 2px solid #fff; }
        .who .n { font-weight: 600; font-size: 15px; }
        .who .e { font-size: 12px; color: var(--dim); }
        label { display: block; font-size: 13px; font-weight: 600; margin: 0 0 6px; }
        input { width: 100%; padding: 12px; font-size: 15px; font-family: inherit; border: 1px solid var(--border); border-radius: 8px; background: #fff; color: var(--text); outline: none; }
        input:focus { border-color: var(--accent); }
        .field { margin-bottom: 16px; }
        .hint { font-size: 12px; color: var(--dim); margin-top: 6px; }
        button { width: 100%; padding: 13px; font-size: 15px; font-family: inherit; font-weight: 600; color: #fff; background: var(--accent); border: 0; border-radius: 9px; cursor: pointer; margin-top: 6px; }
        button:hover { filter: brightness(1.05); }
        .msg { font-size: 13px; color: #c0392b; margin-bottom: 14px; }
        .note { font-size: 12px; color: var(--dim); margin-top: 16px; text-align: center; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="card">
        <div class="brand">SubSync</div>
        <div class="sub">Complete your registration</div>

        <div class="who">
            <img src="{{ $avatar ?: 'https://ui-avatars.com/api/?name='.urlencode($name).'&background=c87941&color=fff&size=120' }}" alt="Profile">
            <div>
                <div class="n">{{ $name }}</div>
                <div class="e">{{ $email }}</div>
            </div>
        </div>

        @if(session('error'))
            <div class="msg">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('google.complete.post') }}">
            @csrf
            <div class="field">
                <label for="block_lot">Block &amp; Lot</label>
                <input id="block_lot" name="block_lot" type="text" maxlength="50" placeholder="e.g. Block 3 Lot 12" value="{{ old('block_lot', $blockLot) }}" required autofocus>
                <div class="hint">Enter your household's block and lot number within Terra Nova.</div>
                @error('block_lot') <div class="msg">{{ $message }}</div> @enderror
            </div>
            <div class="field">
                <label for="contact_number">Contact Number (optional)</label>
                <input id="contact_number" name="contact_number" type="text" maxlength="20" placeholder="e.g. 0917 123 4567" value="{{ old('contact_number', $contactNumber) }}">
            </div>
            <button type="submit">Save &amp; Continue</button>
        </form>

        <div class="note">Your account will remain subject to administrator approval before portal access is granted.</div>
    </div>
</body>
</html>
