# SubSync stop — Reverb + queue worker + scheduler + ngrok (Apache stays up by default).
#   .\scripts\stop-subsync.ps1            stop SubSync background services
#   .\scripts\stop-subsync.ps1 -Apache    also stop Laragon Apache (:80)

param([switch]$Apache)

$ErrorActionPreference = 'SilentlyContinue'

function Stop-PhpProc([string]$pattern, [string]$label) {
    $procs = @(Get-CimInstance Win32_Process -Filter "name='php.exe'" |
        Where-Object { $_.CommandLine -match $pattern })
    if ($procs.Count -eq 0) { Write-Host "  - $label was not running"; return }
    foreach ($p in $procs) { Stop-Process -Id $p.ProcessId -Force -ErrorAction SilentlyContinue }
    Write-Host "  x $label stopped"
}

Write-Host ''
Write-Host 'Stopping SubSync services...' -ForegroundColor Cyan
Stop-PhpProc 'reverb:start'      'Reverb'
Stop-PhpProc 'queue:(work|listen)' 'Queue worker'
Stop-PhpProc 'schedule:work'     'Scheduler'

$ngrok = Get-Process ngrok -ErrorAction SilentlyContinue
if ($ngrok) { $ngrok | Stop-Process -Force; Write-Host '  x ngrok stopped' }
else { Write-Host '  - ngrok was not running' }

if ($Apache) {
    $httpd = Get-Process httpd -ErrorAction SilentlyContinue
    if ($httpd) { $httpd | Stop-Process -Force; Write-Host '  x Apache stopped' }
    else { Write-Host '  - Apache was not running' }
} else {
    Write-Host '  - Apache left running (use -Apache to stop it)'
}
Write-Host ''
