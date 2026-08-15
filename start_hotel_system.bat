@echo off
echo ==============================================
echo       Dara Meas Hotel - System Startup
echo ==============================================
echo.

echo [1/3] Starting Laravel Backend Server...
start "Laravel Server" cmd /c "php artisan serve"

echo [2/3] Starting ngrok Tunnel...
start "ngrok Tunnel" cmd /c "ngrok http --domain=darameashotel.ngrok.dev 8000"

echo [3/3] Starting Telegram Payment Bridge...
start "Telegram Payment Bridge" cmd /k "python userbot_bridge.py"

echo.
echo ==============================================
echo All systems are booting up!
echo 1. Keep all THREE command prompt windows open (PHP, ngrok, and Telegram Bridge).
echo 2. The Telegram Bot window will show you live payment notifications.
echo 3. If you accidentally close the bot window, just run start_hotel_system.bat again.
echo ==============================================
pause
