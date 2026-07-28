[CmdletBinding()]
param(
    [string]$ProjectRoot = (Split-Path -Parent $PSScriptRoot),
    [switch]$SkipLint
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'
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

$phpFiles = @(Get-ChildItem -LiteralPath (Join-Path $ProjectRoot 'app') -Filter '*.php' -Recurse -File -ErrorAction SilentlyContinue)
$phpFiles += @(Get-ChildItem -LiteralPath (Join-Path $ProjectRoot 'public') -Filter '*.php' -Recurse -File -ErrorAction SilentlyContinue)

Write-Result 'Archivos PHP encontrados' ($phpFiles.Count -gt 0) "$($phpFiles.Count) archivo(s)."

$phpCommand = Get-Command php -ErrorAction SilentlyContinue
if ($SkipLint) {
    Write-Result 'PHP lint' $true 'Omitido por parámetro.' -Warning
} elseif ($null -eq $phpCommand) {
    Write-Result 'PHP lint' $true 'PHP no está disponible; se mantiene validación estática.' -Warning
} else {
    foreach ($file in $phpFiles) {
        & $phpCommand.Source -l $file.FullName *> $null
        Write-Result "PHP lint: $($file.Name)" ($LASTEXITCODE -eq 0) 'Sintaxis válida.'
    }
}

$bootstrap = Join-Path $ProjectRoot 'app/bootstrap.php'
$bootstrapText = if (Test-Path $bootstrap) { Get-Content -Raw -LiteralPath $bootstrap } else { '' }
$requiredClasses = @(
    'Core/Database.php',
    'Services/Installer.php',
    'Controllers/SetupController.php',
    'Services/MigrationRunner.php'
)
foreach ($requiredClass in $requiredClasses) {
    Write-Result "Bootstrap incluye $requiredClass" ($bootstrapText -match [regex]::Escape($requiredClass)) 'Referencia encontrada.'
}

$setupController = Join-Path $ProjectRoot 'app/Controllers/SetupController.php'
$setupView = Join-Path $ProjectRoot 'app/Views/setup.php'
$setupText = if (Test-Path $setupController) { Get-Content -Raw -LiteralPath $setupController } else { '' }
$viewText = if (Test-Path $setupView) { Get-Content -Raw -LiteralPath $setupView } else { '' }

Write-Result 'SetupController procesa POST' ($setupText -match "REQUEST_METHOD.*POST") 'Entrada POST encontrada.'
Write-Result 'SetupController valida CSRF' ($setupText -match 'hash_equals' -and $setupText -match "\['csrf'\]") 'Validación CSRF encontrada.'
Write-Result 'Vista setup contiene formulario multipart' ($viewText -match 'method="post"' -and $viewText -match 'multipart/form-data') 'Formulario de instalación encontrado.'
Write-Result 'Vista setup conserva errores' ($viewText -match 'htmlspecialchars\(\$error') 'Salida escapada de errores encontrada.'

$unsafePatterns = @(
    'eval\(',
    'shell_exec\(',
    'system\(',
    'passthru\(',
    'exec\('
)
foreach ($pattern in $unsafePatterns) {
    $matches = @($phpFiles | Select-String -Pattern $pattern -AllMatches)
    Write-Result "Patrón peligroso: $pattern" ($matches.Count -eq 0) 'No encontrado.'
}

if ($failures -gt 0) {
    Write-Host "Validación PHP finalizada con $failures fallo(s) y $warnings advertencia(s)." -ForegroundColor Red
    exit 1
}

Write-Host "Validación PHP finalizada correctamente con $warnings advertencia(s)." -ForegroundColor Green
exit 0
