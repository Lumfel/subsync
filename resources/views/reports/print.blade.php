<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $report->reference_no ?: 'Report' }} — SubSync</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 32px 16px; background: #F3EFE8; color: #2B2927;
               font-family: 'Inter', -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        .sheet { width: 100%; max-width: 760px; margin: 0 auto; background: #fff; border: 1px solid #E6DFD5;
                 border-radius: 8px; padding: 44px 48px; }
        header { display: flex; align-items: center; gap: 14px; border-bottom: 2px solid #A07D53; padding-bottom: 16px; }
        header img { width: 52px; height: 52px; }
        .org { font-size: 18px; font-weight: 700; letter-spacing: .02em; }
        .org-sub { font-size: 12px; color: #7D7975; margin-top: 2px; letter-spacing: .06em; text-transform: uppercase; }
        .meta { display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap;
                font-size: 12.5px; color: #5F5A53; margin: 18px 0 24px; }
        h1 { font-size: 22px; margin: 0 0 18px; }
        .body { white-space: pre-wrap; font-size: 14.5px; line-height: 1.8; }
        footer { margin-top: 48px; display: flex; justify-content: flex-end; }
        .sign { width: 260px; text-align: center; }
        .sign .line { border-top: 1px solid #2B2927; margin-bottom: 6px; }
        .sign .who { font-size: 13.5px; font-weight: 600; }
        .sign .who-sub { font-size: 11px; color: #7D7975; margin-top: 2px; }
        .print-btn { display: block; margin: 28px auto 0; padding: 11px 22px; font: inherit; font-weight: 600; cursor: pointer;
                     background: #A07D53; color: #fff; border: 0; border-radius: 8px; }
        @media print {
            body { background: #fff; padding: 0; }
            .sheet { border: 0; border-radius: 0; max-width: none; padding: 0; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>
    <div class="sheet">
        <header>
            <img src="{{ asset('emblem_1.svg') }}" alt="SubSync">
            <div>
                <div class="org">Terra Nova Homeowners Association</div>
                <div class="org-sub">Official Document · SubSync</div>
            </div>
        </header>

        <div class="meta">
            <span>Reference No: <strong>{{ $report->reference_no ?: '—' }}</strong></span>
            <span>Date issued: {{ $report->created_at?->format('F d, Y') }}</span>
            <span>Resident: {{ $resident?->name ?? '—' }}</span>
        </div>

        <h1>{{ $report->subject }}</h1>

        <div class="body">{{ $report->content }}</div>

        <footer>
            <div class="sign">
                <div class="line"></div>
                <div class="who">{{ $report->issued_by ?: ($resident?->name ?? 'Resident') }}</div>
                <div class="who-sub">{{ $report->issued_by ? 'Issued by (HOA office)' : 'Requested by (self-service)' }}</div>
            </div>
        </footer>

        <button class="print-btn" onclick="window.print()">Print / Save as PDF</button>
    </div>
</body>
</html>
