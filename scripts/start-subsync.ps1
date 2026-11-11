# SubSync start-all — MySQL + Apache + ngrok + Cloudflare + Reverb + queue worker + scheduler.
# Safe to run repeatedly (each service is only started if it isn't already running).
#   .\scripts\start-subsync.ps1            # start everything that's missing
#   .\scripts\start-subsync.ps1 -Status    # just report what's up

param([switch]$Status)

$ErrorActionPreference = 'SilentlyContinue'

$root       = 'C:\laragon\www\subsync'
$php        = 'C:\laragon\bin\php\php-8.4.25-Win32-vs17-x64\php.exe'
$apacheDir  = 'C:/laragon/bin/apache/httpd-2.4.66-260223-Win64-VS18'
$apacheExe  = 'C:\laragon\bin\apache\httpd-2.4.66-260223-Win64-VS18\bin\httpd.exe'
$mysqlExe   = 'C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqld.exe'
$mysqlIni   = 'C:\laragon\bin\mysql\mysql-8.4.3-winx64\my.ini'
$ngrok      = 'C:\laragon\bin\ngrok\ngrok.exe'
$domain     = 'https://preschool-yearling-washout.ngrok-free.dev'
$cloudflared = 'C:\laragon\bin\cloudflared.exe'
$cfLog      = "$root\storage\logs\cloudflared.log"

if (-not (Test-Path $ngrok)) { $ngrok = 'ngrok' }   # fall back to PATH

function Test-Port([int]$port) {
    [bool](Get-NetTCPConnection -LocalPort $port -State Listen -ErrorAction SilentlyContinue)
}
function Get-PhpProc([string]$pattern) {
    Get-CimInstance Win32_Process -Filter "name='php.exe'" |
        Where-Object { $_.CommandLine -match $pattern }
}
function Get-CloudflareUrl {
    if (Test-Path $cfLog) {
        $m = Select-String -Path $cfLog -Pattern 'https://[a-z0-9-]+\.trycloudflare\.com' | Select-Object -Last 1
        if ($m) { return [regex]::Match($m.Line, 'https://[a-z0-9-]+\.trycloudflare\.com').Value }
    }
    return $null
}

function Show-Status {
    $mysql    = Test-Port 3306
    $apache   = Test-Port 80
    $ngrokRun = [bool](Get-Process ngrok -ErrorAction SilentlyContinue)
    $cfRun    = [bool](Get-Process cloudflared -ErrorAction SilentlyContinue)
    $reverb   = [bool](Get-PhpProc 'reverb:start')
    $sched    = [bool](Get-PhpProc 'schedule:work')
    $wsPort   = Test-Port 8080

    Write-Host ''
    Write-Host 'SubSync service status' -ForegroundColor Cyan
    Write-Host ('  {0} MySQL (:3306)     ' -f $(if ($mysql)    { '[ON ]' } else { '[OFF]' }))
    Write-Host ('  {0} Apache (:80)      ' -f $(if ($apache)   { '[ON ]' } else { '[OFF]' }))
    Write-Host ('  {0} ngrok tunnel      ' -f $(if ($ngrokRun) { '[ON ]' } else { '[OFF]' }))
    Write-Host ('  {0} Cloudflare tunnel ' -f $(if ($cfRun)    { '[ON ]' } else { '[OFF]' }))
    Write-Host ('  {0} Reverb (:8080)    ' -f $(if ($reverb -and $wsPort) { '[ON ]' } else { '[OFF]' }))
    Write-Host ('  {0} Scheduler         ' -f $(if ($sched)    { '[ON ]' } else { '[OFF]' }))
    Write-Host ''
    Write-Host ("  Local:  http://subsync.test")
    Write-Host ("  Public: $domain")
    $cfUrl = Get-CloudflareUrl
    if ($cfUrl) {
        Write-Host ("  Backup (mobile networks that block ngrok):") -ForegroundColor Yellow
        Write-Host ("  $cfUrl")
    }
    Write-Host ''
}

if ($Status) { Show-Status; exit 0 }

# 1) MySQL (Laragon) on :3306 — app database
if (-not (Test-Port 3306)) {
    Start-Process -FilePath $mysqlExe -ArgumentList "--defaults-file=$mysqlIni" -WindowStyle Hidden
    for ($i = 0; $i -lt 60; $i++) { Start-Sleep 1; if (Test-Port 3306) { break } }
}

# 2) Apache (Laragon) on :80
if (-not (Test-Port 80)) {
    Start-Process -FilePath $apacheExe -ArgumentList '-d', $apacheDir -WindowStyle Hidden
    Start-Sleep 2
}

# 3) ngrok tunnel -> Apache on :80
if (-not (Get-Process ngrok -ErrorAction SilentlyContinue)) {
    Start-Process -FilePath $ngrok -ArgumentList 'http', '80', '--url', $domain -WindowStyle Hidden
}

# 4) Reverb (WebSocket server on :8080)
if (-not (Get-PhpProc 'reverb:start')) {
    Start-Process -FilePath $php -ArgumentList 'artisan', 'reverb:start' -WorkingDirectory $root -WindowStyle Hidden
}

# 5) Queue worker — intentionally NOT started on this machine: the app has no
#    queued jobs (no app/Jobs, no ShouldQueue; broadcasts are ShouldBroadcastNow),
#    so worker #6 would just idle and burn ~50 MB of RAM. Production keeps one.

# 6) Scheduler (scheduled tasks)
if (-not (Get-PhpProc 'schedule:work')) {
    Start-Process -FilePath $php -ArgumentList 'artisan', 'schedule:work' -WorkingDirectory $root -WindowStyle Hidden
}

# 7) Cloudflare quick tunnel — backup public URL. PH mobile carriers often
#    block *.ngrok-free.dev; *.trycloudflare.com is the fallback for demos on
#    mobile data. The URL is random per start and logged to $cfLog.
if ((Test-Path $cloudflared) -and -not (Get-Process cloudflared -ErrorAction SilentlyContinue)) {
    Start-Process -FilePath $cloudflared `
        -ArgumentList 'tunnel', '--url', 'http://localhost:80', '--no-autoupdate' `
        -RedirectStandardOutput "$root\storage\logs\cloudflared-out.log" `
        -RedirectStandardError $cfLog -WindowStyle Hidden
    for ($i = 0; $i -lt 15; $i++) { Start-Sleep 1; if (Get-CloudflareUrl) { break } }
}

Start-Sleep 3
Show-Status
