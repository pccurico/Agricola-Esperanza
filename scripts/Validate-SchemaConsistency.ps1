[CmdletBinding()]
param([string]$ProjectRoot)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'
$scriptDirectory = $PSScriptRoot
if ([string]::IsNullOrWhiteSpace($ProjectRoot)) {
    $ProjectRoot = Split-Path -Parent $scriptDirectory
}

$phpCommand = Get-Command php -ErrorAction SilentlyContinue
$phpPath = if ($null -ne $phpCommand) { $phpCommand.Source } elseif (Test-Path -LiteralPath 'C:\wamp64\bin\php\php8.2.29\php.exe') { 'C:\wamp64\bin\php\php8.2.29\php.exe' } else { $null }
if ($null -eq $phpPath) {
    Write-Host '[FAIL] Validación de consistencia - PHP no está disponible.' -ForegroundColor Red
    exit 1
}

& $phpPath (Join-Path $scriptDirectory 'Validate-SchemaConsistency.php') $ProjectRoot
exit $LASTEXITCODE
