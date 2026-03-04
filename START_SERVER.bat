@echo off
echo ╔══════════════════════════════════════════════════════════════╗
echo ║              PackTrack - Starting Server                     ║
echo ╚══════════════════════════════════════════════════════════════╝
echo.
echo Server will start at: http://localhost:8000
echo.
echo Admin Login:
echo   Email:    admin@packtrack.com
echo   Password: Admin123!
echo.
echo Press Ctrl+C to stop the server
echo.
echo ════════════════════════════════════════════════════════════════
echo.
php -S localhost:8000 -t public
