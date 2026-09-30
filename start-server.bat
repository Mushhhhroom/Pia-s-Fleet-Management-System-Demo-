@echo off
title FleetPulse FMS Server (Port 8090)
cd /d "%~dp0"
echo ============================================================
echo   FleetPulse - Fleet Management System
echo   Framework: CodeIgniter 4 (PHP 8.4)
echo   Database: MySQL (fleet_db)
echo ============================================================
echo.
echo Starting application server on http://localhost:8090 ...
start "" "http://localhost:8090/login"
php -S 0.0.0.0:8090 -t public vendor\codeigniter4\framework\system\rewrite.php
pause
