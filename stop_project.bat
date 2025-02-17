@echo off
chcp 65001 >nul
echo ⏳ Остановка проекта...

:: Остановка контейнеров
docker-compose down

:: Закрываем Docker Desktop (если нужно)
taskkill /F /IM "Docker Desktop.exe" >nul 2>&1

:: Закрываем вкладку http://localhost:8080/ в браузере через PowerShell
powershell -Command "& {Get-Process | Where-Object {($_.MainWindowTitle -match 'localhost:8080') -or ($_.MainWindowTitle -match 'Currency Exchange')} | ForEach-Object { $_.CloseMainWindow() } }"

echo ✅ Проект остановлен!
exit
