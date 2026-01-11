# Script to upload project to GitHub with title "CAPSTONE TRAINING VERSION"
# Make sure Git is installed before running this script

Write-Host "Checking Git installation..." -ForegroundColor Cyan

# Check if git is available
try {
    $gitVersion = git --version 2>&1
    Write-Host "Git found: $gitVersion" -ForegroundColor Green
} catch {
    Write-Host "ERROR: Git is not installed or not in PATH!" -ForegroundColor Red
    Write-Host "Please install Git from: https://git-scm.com/download/win" -ForegroundColor Yellow
    Write-Host "Make sure to add Git to PATH during installation." -ForegroundColor Yellow
    exit 1
}

Write-Host "`nInitializing Git repository (if needed)..." -ForegroundColor Cyan
if (-not (Test-Path .git)) {
    git init
    Write-Host "Git repository initialized." -ForegroundColor Green
} else {
    Write-Host "Git repository already exists." -ForegroundColor Green
}

Write-Host "`nChecking remote repository..." -ForegroundColor Cyan
$remote = git remote get-url origin 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host "No remote repository configured." -ForegroundColor Yellow
    Write-Host "Please provide your GitHub repository URL (e.g., https://github.com/username/repo.git)" -ForegroundColor Yellow
    $repoUrl = Read-Host "GitHub repository URL"
    if ($repoUrl) {
        git remote add origin $repoUrl
        Write-Host "Remote repository added." -ForegroundColor Green
    } else {
        Write-Host "No URL provided. Skipping remote setup." -ForegroundColor Yellow
        Write-Host "You can add it later with: git remote add origin <your-repo-url>" -ForegroundColor Yellow
    }
} else {
    Write-Host "Remote repository found: $remote" -ForegroundColor Green
}

Write-Host "`nAdding all files to staging..." -ForegroundColor Cyan
git add .

Write-Host "`nCreating commit with message 'CAPSTONE TRAINING VERSION'..." -ForegroundColor Cyan
git commit -m "CAPSTONE TRAINING VERSION"

if ($LASTEXITCODE -eq 0) {
    Write-Host "Commit created successfully!" -ForegroundColor Green
} else {
    Write-Host "No changes to commit or commit failed." -ForegroundColor Yellow
}

Write-Host "`nPushing to GitHub..." -ForegroundColor Cyan
$branch = git branch --show-current 2>&1
if (-not $branch) {
    $branch = "main"
    git branch -M main
}

try {
    git push -u origin $branch
    Write-Host "`nSuccessfully pushed to GitHub!" -ForegroundColor Green
    Write-Host "Repository: $remote" -ForegroundColor Cyan
    Write-Host "Branch: $branch" -ForegroundColor Cyan
    Write-Host "Commit message: CAPSTONE TRAINING VERSION" -ForegroundColor Cyan
} catch {
    Write-Host "`nPush failed. You may need to:" -ForegroundColor Yellow
    Write-Host "1. Set up authentication (GitHub Personal Access Token)" -ForegroundColor Yellow
    Write-Host "2. Check your remote URL" -ForegroundColor Yellow
    Write-Host "3. Ensure you have push access to the repository" -ForegroundColor Yellow
}

Write-Host "`nDone!" -ForegroundColor Green
