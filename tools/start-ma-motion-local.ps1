param(
    [string]$ProjectPath = "C:\laravel-projects\ma_motion_backend",
    [string]$PhpPath = "C:\wamp64\bin\php\php8.4.0\php.exe",
    [string]$HostAddress = "127.0.0.1",
    [int]$Port = 8001
)

$ErrorActionPreference = 'Stop'

$ProjectPath = (Resolve-Path $ProjectPath).Path
if (-not (Test-Path (Join-Path $ProjectPath 'artisan'))) {
    throw "Laravel artisan file not found at $ProjectPath."
}
if (-not (Test-Path $PhpPath)) {
    throw "PHP executable not found at $PhpPath."
}

Push-Location $ProjectPath
try {
    $env:APP_URL = "http://${HostAddress}:$Port"

    Write-Host "MA Motion local Laravel server" -ForegroundColor Magenta
    Write-Host "Project: $ProjectPath"
    Write-Host "PHP:     $PhpPath"
    Write-Host "URL:     http://${HostAddress}:$Port/admin/login" -ForegroundColor Green
    Write-Host ""
    Write-Host "Keep this PowerShell window open while using the Admin Panel." -ForegroundColor Yellow
    Write-Host "Press Ctrl+C here when you want to stop the Laravel server." -ForegroundColor Yellow
    Write-Host ""

    & $PhpPath artisan optimize:clear
    if ($LASTEXITCODE -ne 0) {
        throw 'Laravel optimize:clear failed.'
    }

    & $PhpPath artisan serve --host=$HostAddress --port=$Port
}
finally {
    Pop-Location
}
