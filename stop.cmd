@echo off
REM SubSync - stop background services (Reverb, queue worker, scheduler, ngrok)
REM Usage:  stop.cmd            stop SubSync background services
REM         stop.cmd -Apache    also stop Laragon Apache
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\stop-subsync.ps1" %*
pause
