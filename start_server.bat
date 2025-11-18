@echo off
echo E-commerce RESTful API - PHP Implementation
echo ==========================================
echo.

REM Check if PHP is installed
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: PHP is not installed or not in the system PATH.
    echo.
    echo Please install PHP 7.4 or higher and add it to your PATH.
    echo Download PHP from: https://www.php.net/downloads
    echo.
    echo After installing PHP, run this script again.
    pause
    exit /b 1
)

echo PHP is installed. Starting the development server...
echo.
echo The API will be available at: http://localhost:8000
echo Press Ctrl+C to stop the server
echo.

REM Start the PHP development server
php -S localhost:8000 -t public

pause