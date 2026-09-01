$active = ".env"
$local = ".env.local"
$prod = ".env.production"

if (!(Test-Path $local) -or !(Test-Path $prod)) {
    Write-Host "Missing .env.local or .env.production" -ForegroundColor Red
    exit 1
}

if (!(Test-Path $active)) {
    # No .env exists, start with local
    Copy-Item $local $active
    Write-Host "[LOCAL] Copied .env.local -> .env" -ForegroundColor Green
} else {
    $current = Get-Content $active -First 1
    $localFirst = Get-Content $local -First 1

    if ($current -eq $localFirst) {
        # Currently local -> switch to production
        Copy-Item $prod $active -Force
        Write-Host "[PRODUCTION] Copied .env.production -> .env" -ForegroundColor Red
    } else {
        # Currently production -> switch to local
        Copy-Item $local $active -Force
        Write-Host "[LOCAL] Copied .env.local -> .env" -ForegroundColor Green
    }
}
