@echo off
REM SubSync - start all services (Apache, ngrok, Reverb, queue worker, scheduler)
REM Usage:  start.cmd            start anything that's missing
REM         start.cmd -Status    only show status
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\start-subsync.ps1" %*
pause
