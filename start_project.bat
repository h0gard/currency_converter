@echo off
chcp 65001 >nul
echo Проверка, запущен ли Docker...

:: Проверяем, запущен ли процесс Docker Desktop
tasklist | findstr /I "Docker Desktop.exe" >nul
if %ERRORLEVEL% equ 0 (
    echo ✅ Docker Desktop уже запущен!
) else (
    echo 🟡 Docker Desktop не запущен, запускаем...
    start "" "C:\Program Files\Docker\Docker\Docker Desktop.exe"
    timeout /t 10 /nobreak >nul
)

:: Проверяем, запущен ли сам Docker (демон)
docker info >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo ❌ Ошибка! Docker демон не запущен. Проверьте вручную.
    exit /b
)

echo ✅ Docker работает!

:: Запуск проекта
cd /d C:\Program Files\Ampps\www\projects\currency_exchange
echo 🚀 Запускаем контейнеры...
docker-compose up -d --build

:: Ожидание старта контейнеров
timeout /t 5 /nobreak >nul

:: Открываем сайт в браузере
start http://localhost:8080/

echo ✅ Проект успешно запущен!
exit
