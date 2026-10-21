# SubSync start-all — MySQL + Apache + ngrok + Reverb + queue worker + scheduler.
# Safe to run repeatedly (each service is only started if it isn't already running).
#   .\scripts\start-subsync.ps1            # start everything that's missing
#   .\scripts\start-subsync.ps1 -Status    # just report what's up

param([switch]$Status)

$ErrorActionPreference = 'SilentlyContinue'

$root      = 'C:\laragon\www\subsync'
$php       = 'C:\laragon\bin\php\php-8.4.25-Win32-vs17-x64\php.exe'
$apacheDir = 'C:/laragon/bin/apache/httpd-2.4.66-260223-Win64-VS18'
$apacheExe = 'C:\laragon\bin\apache\httpd-2.4.66-260223-Win64-VS18\bin\httpd.exe'
$mysqlExe  = 'C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqld.exe'
$mysqlIni  = 'C:\laragon\bin\mysql\mysql-8.4.3-winx64\my.ini'
$ngrok     = 'C:\laragon\bin\ngrok\ngrok.exe'
$domain    = 'https://preschool-yearling-washout.ngrok-free.dev'

if (-not (Test-Path $ngrok)) { $ngrok = 'ngrok' }   # fall back to PATH

function Test-Port([int]$port) {
    [bool](Get-NetTCPConnection -LocalPort $port -State Listen -ErrorAction SilentlyContinue)
}
function Get-PhpProc([string]$pattern) {
    Get-CimInstance Win32_Process -Filter "name='php.exe'" |
        Where-Object { $_.CommandLine -match $pattern }
}

function Show-Status {
    $mysql    = Test-Port 3306
    $apache   = Test-Port 80
    $ngrokRun = [bool](Get-Process ngrok -ErrorAction SilentlyContinue)
    $reverb   = [bool](Get-PhpProc 'reverb:start')
    $queue    = [bool](Get-PhpProc 'queue:(work|listen)')
    $sched    = [bool](Get-PhpProc 'schedule:work')
    $wsPort   = Test-Port 8080

    Write-Host ''
    Write-Host 'SubSync service status' -ForegroundColor Cyan
    Write-Host ('  {0} MySQL (:3306)     ' -f $(if ($mysql)    { '[ON ]' } else { '[OFF]' }))
    Write-Host ('  {0} Apache (:80)      ' -f $(if ($apache)   { '[ON ]' } else { '[OFF]' }))
    Write-Host ('  {0} ngrok tunnel      ' -f $(if ($ngrokRun) { '[ON ]' } else { '[OFF]' }))
    Write-Host ('  {0} Reverb (:8080)    ' -f $(if ($reverb -and $wsPort) { '[ON ]' } else { '[OFF]' }))
    Write-Host ('  {0} Queue worker      ' -f $(if ($queue)    { '[ON ]' } else { '[OFF]' }))
    Write-Host ('  {0} Scheduler         ' -f $(if ($sched)    { '[ON ]' } else { '[OFF]' }))
    Write-Host ''
    Write-Host ("  Local:  http://subsync.test")
    Write-Host ("  Public: $domain")
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

# 5) Queue worker (background jobs)
if (-not (Get-PhpProc 'queue:(work|listen)')) {
    Start-Process -FilePath $php -ArgumentList 'artisan', 'queue:work', '--tries=3', '--timeout=90' -WorkingDirectory $root -WindowStyle Hidden
}

# 6) Scheduler (scheduled tasks)
if (-not (Get-PhpProc 'schedule:work')) {
    Start-Process -FilePath $php -ArgumentList 'artisan', 'schedule:work' -WorkingDirectory $root -WindowStyle Hidden
}

Start-Sleep 3
Show-Status
