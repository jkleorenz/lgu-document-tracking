# LGU Document Tracking System - Environment Setup Script
# This script sets up PHP, Composer, and Node.js for the Laravel project

Write-Host "Setting up development environment..." -ForegroundColor Green

# Check if PHP exists
if (Test-Path "C:\xampp\php\php.exe") {
    Write-Host "✓ PHP found at C:\xampp\php\php.exe" -ForegroundColor Green
    
    # Add PHP to PATH if not already there
    $currentPath = [Environment]::GetEnvironmentVariable("Path", "User")
    if ($currentPath -notlike "*C:\xampp\php*") {
        [Environment]::SetEnvironmentVariable("Path", $currentPath + ";C:\xampp\php", "User")
        Write-Host "✓ Added PHP to PATH" -ForegroundColor Green
    } else {
        Write-Host "✓ PHP already in PATH" -ForegroundColor Yellow
    }
} else {
    Write-Host "✗ PHP not found at C:\xampp\php\php.exe" -ForegroundColor Red
    Write-Host "  Please install XAMPP or update the path in this script" -ForegroundColor Yellow
}

# Check if Composer exists
$composerPath = "$env:LOCALAPPDATA\Programs\Composer\bin\composer.bat"
if (Test-Path $composerPath) {
    Write-Host "✓ Composer found" -ForegroundColor Green
    
    # Add Composer to PATH if not already there
    $currentPath = [Environment]::GetEnvironmentVariable("Path", "User")
    if ($currentPath -notlike "*$env:LOCALAPPDATA\Programs\Composer\bin*") {
        [Environment]::SetEnvironmentVariable("Path", $currentPath + ";$env:LOCALAPPDATA\Programs\Composer\bin", "User")
        Write-Host "✓ Added Composer to PATH" -ForegroundColor Green
    } else {
        Write-Host "✓ Composer already in PATH" -ForegroundColor Yellow
    }
} else {
    Write-Host "✗ Composer not found. Installing..." -ForegroundColor Yellow
    
    # Create Composer directory
    New-Item -ItemType Directory -Force -Path "$env:LOCALAPPDATA\Programs\Composer" | Out-Null
    New-Item -ItemType Directory -Force -Path "$env:LOCALAPPDATA\Programs\Composer\bin" | Out-Null
    
    # Download and install Composer
    $composerInstaller = "$env:TEMP\composer-setup.php"
    try {
        Invoke-WebRequest -Uri "https://getcomposer.org/installer" -OutFile $composerInstaller
        & "C:\xampp\php\php.exe" $composerInstaller --install-dir="$env:LOCALAPPDATA\Programs\Composer" --filename=composer.phar
        
        # Create composer.bat wrapper
        @"
@echo off
php "%~dp0..\composer.phar" %*
"@ | Out-File -FilePath "$env:LOCALAPPDATA\Programs\Composer\bin\composer.bat" -Encoding ASCII
        
        # Add to PATH
        $currentPath = [Environment]::GetEnvironmentVariable("Path", "User")
        [Environment]::SetEnvironmentVariable("Path", $currentPath + ";$env:LOCALAPPDATA\Programs\Composer\bin", "User")
        
        Write-Host "✓ Composer installed successfully" -ForegroundColor Green
    } catch {
        Write-Host "✗ Failed to install Composer: $_" -ForegroundColor Red
    }
}

# Check if Node.js is installed
try {
    $nodeVersion = node --version 2>$null
    if ($nodeVersion) {
        Write-Host "✓ Node.js found: $nodeVersion" -ForegroundColor Green
    }
} catch {
    Write-Host "✗ Node.js not found. Please install from https://nodejs.org/" -ForegroundColor Red
    Write-Host "  Or run: winget install OpenJS.NodeJS.LTS" -ForegroundColor Yellow
}

# Refresh PATH for current session
$env:Path = [System.Environment]::GetEnvironmentVariable("Path","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("Path","User")

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

# Verify npm
try {
    $npmVersion = npm --version 2>&1
    Write-Host "✓ npm: $npmVersion" -ForegroundColor Green
} catch {
    Write-Host "✗ npm not accessible" -ForegroundColor Red
}

Write-Host "`nSetup complete! You may need to restart your terminal for PATH changes to take effect." -ForegroundColor Green
Write-Host "`nNext steps:" -ForegroundColor Cyan
Write-Host "  1. composer install" -ForegroundColor White
Write-Host "  2. npm install" -ForegroundColor White
Write-Host "  3. php artisan key:generate" -ForegroundColor White
Write-Host "  4. php artisan migrate --seed" -ForegroundColor White
Write-Host "  5. php artisan serve" -ForegroundColor White
