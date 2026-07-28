[CmdletBinding()]
param(
    [string]$ProjectRoot = (Split-Path -Parent $PSScriptRoot),
    [switch]$SkipPhpLint
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$failures = 0
$warnings = 0

function Write-Result {
    param(
        [string]$Name,
        [bool]$Passed,
        [string]$Message,
        [switch]$Warning
    )

    if ($Passed) {
        Write-Host "[OK]   $Name - $Message" -ForegroundColor Green
        return
    }

    if ($Warning) {
        $script:warnings++
        Write-Host "[WARN] $Name - $Message" -ForegroundColor Yellow
        return
    }

    $script:failures++
    Write-Host "[FAIL] $Name - $Message" -ForegroundColor Red
}

function Test-RequiredPath {
    param([string]$RelativePath)

    $path = Join-Path $ProjectRoot $RelativePath
    Write-Result "Archivo requerido: $RelativePath" (Test-Path -LiteralPath $path) "Ruta verificada."
}

$requiredPaths = @(
    'app/bootstrap.php',
    'app/Services/Installer.php',
    'app/Services/MigrationRunner.php',
    'app/Controllers/SetupController.php',
    'app/Views/setup.php',
    'config/config.example.php',
    'database/schema.sql',
    'database/migrations',
    'database/seeds'
)

foreach ($requiredPath in $requiredPaths) {
    Test-RequiredPath $requiredPath
}

& (Join-Path $PSScriptRoot 'Validate-Php.ps1') -ProjectRoot $ProjectRoot -SkipLint:$SkipPhpLint
if ($LASTEXITCODE -ne 0) {
    $failures++
}

& (Join-Path $PSScriptRoot 'Validate-Sql.ps1') -ProjectRoot $ProjectRoot
if ($LASTEXITCODE -ne 0) {
    $failures++
}

$installer = Join-Path $ProjectRoot 'app/Services/Installer.php'
$frontController = Join-Path $ProjectRoot 'public/index.php'
$installerText = if (Test-Path $installer) { Get-Content -Raw -LiteralPath $installer } else { '' }
$frontControllerText = if (Test-Path $frontController) { Get-Content -Raw -LiteralPath $frontController } else { '' }

Write-Result 'Wizard usa schema oficial' ($installerText -match "database/schema\.sql") 'Installer referencia database/schema.sql.'
Write-Result 'Wizard registra configuración' ($installerText -match 'config\.example\.php' -and $installerText -match 'rename\(') 'Installer genera config/config.php.'
Write-Result 'Front controller detecta instalación' ($frontControllerText -match 'config/config\.php' -and $frontControllerText -match 'SetupController') 'Ruta inicial del wizard encontrada.'
Write-Result 'Wizard protegido por CSRF' ($frontControllerText -match 'verify_csrf' -or $installerText -match 'setup_csrf') 'Protección CSRF encontrada en el flujo de instalación.'

if ($failures -gt 0) {
    Write-Host "Validación finalizada con $failures fallo(s) y $warnings advertencia(s)." -ForegroundColor Red
    exit 1
}

Write-Host "Validación finalizada correctamente con $warnings advertencia(s)." -ForegroundColor Green
exit 0
