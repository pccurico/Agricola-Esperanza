[CmdletBinding()]
param(
    [string]$ProjectRoot
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$dotSourced = $MyInvocation.InvocationName -eq '.'
$scriptDirectory = if (-not [string]::IsNullOrWhiteSpace($PSScriptRoot)) { $PSScriptRoot } else { Split-Path -Parent $MyInvocation.MyCommand.Path }
if ([string]::IsNullOrWhiteSpace($ProjectRoot)) {
    $ProjectRoot = Split-Path -Parent $scriptDirectory
}

$failures = 0
$warnings = 0

function Write-Result {
    param([string]$Name, [bool]$Passed, [string]$Message, [switch]$Warning)

    if ($Passed) {
        Write-Host "[OK]   $Name - $Message" -ForegroundColor Green
    } elseif ($Warning) {
        $script:warnings++
        Write-Host "[WARN] $Name - $Message" -ForegroundColor Yellow
    } else {
        $script:failures++
        Write-Host "[FAIL] $Name - $Message" -ForegroundColor Red
    }
}

$javascriptPath = Join-Path $ProjectRoot 'public/assets/js'
$javascriptFiles = @(Get-ChildItem -LiteralPath $javascriptPath -Filter '*.js' -Recurse -File -ErrorAction SilentlyContinue)
Write-Result 'JavaScript files found' ($javascriptFiles.Count -gt 0) "$($javascriptFiles.Count) file(s)."

$nodeCommand = Get-Command node -ErrorAction SilentlyContinue
if ($null -eq $nodeCommand) {
    Write-Result 'JavaScript syntax' $true 'Node.js is not available; syntax validation was skipped.' -Warning
} else {
    foreach ($file in $javascriptFiles) {
        & $nodeCommand.Source --check $file.FullName *> $null
        Write-Result "JavaScript syntax: $($file.Name)" ($LASTEXITCODE -eq 0) 'Valid syntax.'
    }
}

if ($failures -gt 0) {
    Write-Host "JavaScript validation completed with $failures failure(s) and $warnings warning(s)." -ForegroundColor Red
    if ($dotSourced) { return }
    exit 1
}

Write-Host "JavaScript validation completed with $warnings warning(s)." -ForegroundColor Green
if ($dotSourced) { return }
exit 0
