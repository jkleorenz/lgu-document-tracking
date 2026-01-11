# Quick PATH refresh script - Run this in your PowerShell window if PHP is not recognized

Write-Host "Refreshing PATH..." -ForegroundColor Cyan

# Refresh PATH from environment variables
$env:Path = [System.Environment]::GetEnvironmentVariable("Path","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("Path","User")

# Also ensure PHP and Composer are in PATH for this session
if ($env:Path -notlike "*C:\xampp\php*") {
    $env:Path += ";C:\xampp\php"
}

if ($env:Path -notlike "*$env:LOCALAPPDATA\Programs\Composer\bin*") {
    $env:Path += ";$env:LOCALAPPDATA\Programs\Composer\bin"
}

Write-Host "PATH refreshed!" -ForegroundColor Green
Write-Host "`nVerifying installations..." -ForegroundColor Cyan

# Verify PHP
try {
    $phpVersion = php -v 2>&1 | Select-Object -First 1
    Write-Host "✓ PHP: $phpVersion" -ForegroundColor Green
} catch {
    Write-Host "✗ PHP not accessible" -ForegroundColor Red
}

# Verify Composer
try {
    $composerVersion = composer --version 2>&1 | Select-Object -First 1
    Write-Host "✓ Composer: $composerVersion" -ForegroundColor Green
} catch {
    Write-Host "✗ Composer not accessible" -ForegroundColor Red
}

# Verify Node.js
try {
    $nodeVersion = node --version 2>&1
    Write-Host "✓ Node.js: $nodeVersion" -ForegroundColor Green
} catch {
    Write-Host "✗ Node.js not accessible" -ForegroundColor Red
}

Write-Host "`nYou can now run: php artisan serve" -ForegroundColor Green
